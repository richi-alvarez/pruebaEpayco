<?php

namespace App\Services;

use Firebase\JWT\JWT;
use App\Entity\User;

class JwtAuth{

    public $manager;
    public $key;

    public function __construct($manager){
        $this->manager = $manager;
        $this->key = '19520xr9Lo97POl812';
    }

    public function signup($email, $password, $gettoken =null){
        //validar si existe el usuario
        $user = $this->manager->getRepository(User::class)->findOneBy([
            'email'=>$email,
            'password'=>$password
        ]);
        $signup=false;
        if(is_object($user)){
            $signup=true;
        }
        //si existe, generar token jwt
        if($signup){
            $token = [
                'sub'=>$user->getId(),
                'name'=>$user->getName(),
                'email'=>$user->getEmail(),
                'iat'=>time(),
                'exp'=>time()+(1*24*60*60),
            ];
            //comprobar el flag gettoken, condición
            $jwt=JWT::encode($token, $this->key, 'HS256');
            if(!empty($gettoken)){   
                $data = $jwt;
            }else{
                $decoded=JWT::decode($jwt, $this->key, ['HS256']);
                $data = $decoded;
            }
           
        }else{
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Login incorrecto'
            ];
        }
       

        //devolver datos

        return $data;
    }
}