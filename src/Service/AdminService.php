<?php

namespace Bike\Partner\Service;

use Bike\Partner\Exception\Debug\DebugException;
use Bike\Partner\Exception\Logic\LogicException;
use Bike\Partner\Service\AbstractService;
use Bike\Partner\Util\ArgUtil;
use Bike\Partner\Db\Partner\Admin;
use Bike\Partner\Db\Partner\Passport;

class AdminService extends AbstractService
{
    public function createAdmin(array $data)
    {
        $data = ArgUtil::getArgs($data, array(
            'name',
            'username',
            'pwd',
            'repwd',
        ));
        $data['type'] = Passport::TYPE_ADMIN;

        $this->validateName($data['name']);
        $adminDao = $this->getAdminDao();
        $adminConn = $adminDao->getConn();
        $passportService = $this->container->get('bike.partner.service.passport');
        $passportDao = $this->container->get('bike.partner.dao.partner.passport');
        $passportConn = $passportDao->getConn();
        $adminConn->beginTransaction();
        $passportConn->beginTransaction();
        try {
            $passportId = $passportService->createPassport($data);
            $admin = new Admin($data);
            $admin->setId($passportId);
            $adminDao->create($admin);

            $passportConn->commit();
            $adminConn->commit();
        } catch (\Exception $e) {
            $passportConn->rollBack();
            $adminConn->rollBack();
            throw $e;
        }
    }


    public function editAdmin($id, array $data)
    {
        $data = ArgUtil::getArgs($data, array(
            'name',
            'username',
            'pwd',
            'repwd',
        ));
        $data['type'] = Passport::TYPE_ADMIN;
        $data['id'] = $id;

        $this->validateName($data['name']);
        $adminDao = $this->getAdminDao();
        $adminConn = $adminDao->getConn();
        $passportService = $this->container->get('bike.partner.service.passport');
        $passportDao = $this->container->get('bike.partner.dao.partner.passport');
        $passportConn = $passportDao->getConn();
        $adminConn->beginTransaction();
        $passportConn->beginTransaction();
        try {
            $passportService->updatePassport($id,$data);
            $admin = new Admin($data);
            $adminDao->save($admin);

            $passportConn->commit();
            $adminConn->commit();
        } catch (\Exception $e) {
            $passportConn->rollBack();
            $adminConn->rollBack();
            throw $e;
        }   

    }


    public function getAdmin($id)
    {
        $key = 'admin.' . $id;
        $admin = $this->getRequestCache($key);
        if (!$admin) {
            $adminDao = $this->getAdminDao();
            $admin = $adminDao->find($id);
            if ($admin) {
                $this->setRequestCache($key, $admin);
            }
        }
        return $admin;
    }

    public function searchAdmin(array $args, $page, $pageNum)
    {
        $page = intval($page);
        $pageNum = intval($pageNum);
        if ($page < 1) {
            $page = 1;
        }
        if ($pageNum < 1) {
            $pageNum = 1;
        }
        $offset = ($page - 1) * $pageNum;
        $adminDao = $this->getAdminDao();
        $adminList = $adminDao->findList('*', $args, $offset, $pageNum);
        if ($adminList) {
            $passportIds = array();
            foreach ($adminList as $v) {
                $passportIds[] = $v->getId();
            }
            $passportDao = $this->container->get('bike.partner.dao.partner.passport');
            $passportMap = $passportDao->findMap('', array(
                'id.in' => $passportIds,
            ), 0, 0);
        } else {
            $passportMap = array();
            $adminList = array();
        }
        $total = $adminDao->findNum($args);
        if ($total) {
            $totalPage = ceil($total / $pageNum);
            if ($page > $totalPage) {
                $page = $totalPage;
            }
        } else {
            $totalPage = 1;
            $page = 1;
        }

        return array(
            'page' => $page,
            'totalPage' => $totalPage,
            'pageNum' => $pageNum,
            'total' => $total,
            'list' => array(
                'admin' => $adminList,
            ),
            'map' => array(
                'passport' => $passportMap,
            ),
        );
    }

    protected function validateName($name)
    {
        if (!$name) {
            throw new LogicException('管理员名称不能为空');
        }
        $len = mb_strlen($name);
        if ($len > 20) {
            throw new LogicException('管理员名称不能多于20个字符');
        }
    }

    protected function getAdminDao()
    {
        return $this->container->get('bike.partner.dao.partner.admin');
    }
}
 
