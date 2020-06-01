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
    public function checkToken($jwt, $identity = false){
		$auth = false;
			try{
					$decoded = JWT::decode($jwt,$this->key,['HS256']);
				}catch(\UnexpectedValueException $e){
					$auth = false;
				}catch(\DomainException $e){
					$auth = false;
				}
	
			if (isset($decoded) && !empty($decoded) && is_object($decoded) && isset($decoded->sub) ) {
					$auth = true;
				}else{
					$auth = false;
			}

			if ($identity != false) {
				return $decoded;
			}else{
				return $auth;
			}
        }
        
    public function paymentToken($user_id,$documento,$celular,$valor){
        $jwt = [
            'sub' => $user_id,
            'documento' => $documento,
            'celular' => $celular,
            'pagar' => $valor,
            'iar' => time(),
            'exp' => time() + (60 * 15)
        ];
        $id_session = JWT::encode($jwt, 'pago', 'HS256');
        return $id_session;
        }

    public function checkTokenPayment($token_seccion){
            $auth = false;
                try{
                    $decoded = JWT::decode($token_seccion,'pago',['HS256']);
                    $auth = true;
                    }catch(\UnexpectedValueException $e){
                        $auth = false;
                    }catch(\DomainException $e){
                        $auth = false;
                    }
        
            if (isset($decoded) && !empty($decoded) && is_object($decoded) && isset($decoded->sub) ) {
            
                    return $decoded;
                
            }else{
                return $auth;
            }
        }

}