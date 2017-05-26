<?php

namespace Bike\Partner\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Bike\Partner\Exception\Logic\LogicExceptionInterface;
use Bike\Partner\Error\ErrorCode;

abstract class AbstractController extends Controller
{
    protected function jsonSuccess($data = null)
    {
        $result = array(
            'errno' => ErrorCode::SUCCESS,
            'errmsg' => '',
        );
        if ($data !== null) {
            $result['data'] = $data;
        }
        return $this->json($result);
    }

    protected function jsonError($errno, $defaultErrmsg = null, $data = null)
    {
        if ($errno instanceof LogicExceptionInterface) {
            $result = array(
                'errno' => $errno->getCode(),
                'errmsg' => $errno->getMessage(),
            );
        } else {
            $errmsg = '出错了';
            if ($defaultErrmsg) {
                $errmsg = $defaultErrmsg;
            }
            $result = array(
                'errno' => ErrorCode::LOGIC_ERROR,
                'errmsg' => $errmsg,
            );
        }

        if ($data !== null) {
            $result['data'] = $data;
        }

        return $this->json($result);
    }
}
