<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;
use App\Entity\User;
use App\Entity\Whallet;
use App\Services\JwtAuth;
use Firebase\JWT\JWT;

class WhalletController extends AbstractController
{
    private function resjson($data){
        //Serializar datos con servicio serializer
        $json = $this->get('serializer')->serialize($data, 'json');
        //response con httpfoundation
        $response = new Response();
        //Asignar contenido a la respuesta
        $response->setContent($json);
        //Indicar formato de respuesta
        $response->headers->set('Content-Type', 'application/json');
        //Devolver la respuesta
        return $response;
    }

    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/WhalletController.php',
        ]);
    }
    public function recargar(Request $request, JwtAuth $jwt_auth ){
       //recoger los datos por post
        $params = json_decode($request->getContent(), true);
         //recojer el tocken
        $tokenArray= $params[0];
        $cadena_limpia = str_replace('"\"', '', $tokenArray);
        $token = $cadena_limpia;
        //comprar si es correcto el token
        $authCheck = $jwt_auth->checkToken($token);
        if($authCheck){
        //recoger el objeto del usuario identificado
        $identity = $jwt_auth->checkToken($token, true);
        //comprombar y validar datos
        if(!empty($params))
        {
            $user_id = ($identity->sub != null) ? $identity->sub : null;
            $documento = $params['documento'];
            $celular = $params['celular'];
            $saldo = $params['saldo'];
            if(!empty($user_id)){
                $em = $this->getDoctrine()->getManager();
                $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                    'id'=>$user_id
                ]);
                if($user){
                    /* validar que los datos del cliente logueado concuerden con los datos ingresados*/ 
                    $algo = [$user];
                    $something2= json_encode($algo);
                    $data2 = json_decode($something2, true);
                    $user_whallet_id=$data2[0]['id'];
                    $user_whallet_documento=$data2[0]['documento'];
                    $user_whallet_celular=$data2[0]['celular'];
                    if($celular ==  $user_whallet_celular &&  $documento ==  $user_whallet_documento){
                    //validar existe la billetera
                    $whalletArray=$user->getWhallets();
                    $countWhallet = count($whalletArray);
                    if($countWhallet>0){
                        foreach ($user->getWhallets() as $whallet) {
                            $saldo_whallet = $whallet->getSaldo();
                            $id_whallet=$whallet->getId();
                           }
                           $exist=true;
                    }else{
                        $exist=false;
                    }
                           if($exist){
                               //si tiene saldo aumentarle
                            $whallet = $this->getDoctrine()->getRepository(Whallet::class)->findOneBy([
                                'id'=>$id_whallet
                            ]);
                          //  agregar el nuevo saldo al existente
                            $pagar = $saldo;
                            $nuevo_saldo = $saldo_whallet+$pagar;
                            $em = $this->getDoctrine()->getManager(); 
                            $whallet->setToken('');
                            $whallet->setSession('');
                            $whallet->setSaldo($nuevo_saldo);
                            //guardar bd
                            $em->persist($whallet);
                            $em->flush();
                            $data = [
                                'status' => 'success',
                                'code' => 200,
                                'message' => 'se recargo el saldo existente'
                                 
                            ];
                           }else{
                               // crear billetera
                            $whallet = new Whallet();
                            $whallet->setUser($user);
                            $whallet->setSaldo($saldo);
                            $whallet->setToken('');
                            $whallet->setSession('');
                            $createdAt = new \Datetime('now');
                            $whallet->setCreatedAt($createdAt);
                            //guardar bd
                            $em->persist($whallet);
                            $em->flush();
                            $data = [
                                'status' => 'success',
                                'code' => 200,
                                'message' => 'la recarga fue exitosa'
                            ];

                           }
                   
                    }else{
                        $data = [
                            'status' => 'error',
                            'code' => 400,
                            'message' => 'la cedula o celular no se encuentran registrados!'
                        ];
                    }

          
                }else{
                    $data = [
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'la cedula o celular no se encuentran registrados!'
                    ];
                }
            }
        }
        }else{
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'authentificacion fallida!'
            ];
        }
        return $this->resjson($data);
    }

    public function pagar(Request $request, JwtAuth $jwt_auth ){
        //recoger los datos por post
         $params = json_decode($request->getContent(), true);
          //recojer el tocken
         $tokenArray= $params[0];
         $texto = preg_replace('([^A-Za-z0-9 ])', '', $tokenArray);
         $cadena_limpia = str_replace('"\"', '', $tokenArray);
         $token = $cadena_limpia;
         //comprar si es correcto el token
         $authCheck = $jwt_auth->checkToken($token);
         if($authCheck){ 
         //recoger el objeto del usuario identificado
         $identity = $jwt_auth->checkToken($token, true);
         //comprombar y validar datos
         if(!empty($params))
         {
             $user_id = ($identity->sub != null) ? $identity->sub : null;
             $documento = $params['documento'];
             $celular = $params['celular'];
             $valor = $params['saldo'];
            
             if(!empty($user_id)){
                 $em = $this->getDoctrine()->getManager(); 
                $usuario = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                    'id'=>$user_id
                ]);
                
                if($usuario){
                foreach ($usuario->getWhallets() as $whallet) {
                 $saldo_whallet = $whallet->getSaldo();
                 $id_whallet=$whallet->getId();
                }
                //validar si tiene saldo
                if($saldo_whallet>=$valor){
                    $whallet = $this->getDoctrine()->getRepository(Whallet::class)->findOneBy([
                        'id'=>$id_whallet
                    ]);
                    //generar token de validación
                    $token = bin2hex(random_bytes(3));
                    $id_session = $jwt_auth->paymentToken($user_id,$documento,$celular,$valor);
                                // Envio de correo
                              //  $destino = $usuario->getEmail();
                                $destino = 'ric.salda.94@gmail.com';
                                $asunto = "confirmacion para realizar pago";
                                $contenido = "Confirmar pago con los siguientes datos \n";
                                $contenido .= "Token : $token \n";
                                $contenido .= "Id_session: $id_session \n";
                           //    mail($destino, $asunto, $contenido);
                        //set token 
                        $whallet->setToken($token);
                        $whallet->setSession($id_session);
                        $whallet->setSaldo($saldo_whallet);
                        //guardar bd
                        $em->persist($whallet);
                        $em->flush();
                                //respuesta con id de sesion(jwt) y con token
                                $data = [
                                    'status' => 'success',
                                    'code' => 200,
                                    'message' => 'Pago: se envio un email de confirmación de pago para continuar con el proceso',
                                    'token' => $token,
                                    'id_session' => $id_session
                                ];
                }else{
                    $data = [
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'no tiene saldo suficiente para reaizar el pago!',
                        'saldo actual'=>  $saldo_whallet." $"
                    ];
                }
                 
                }
                else{
                    $data = [
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'authentificacion fallida!'
                    ];
                 }
             }
          }
         }else{
             $data = [
                 'status' => 'error',
                 'code' => 400,
                 'message' => 'authentificacion fallida!'
             ];
         }
         return $this->resjson($data);
     }

     public function confirmar(Request $request, JwtAuth $jwt_auth ){
        //recoger los datos por post
       $params = json_decode($request->getContent(), true);
          //recojer el tocken
         $tokenArray= $params[0];
         $idSessionArray= $params[1];
         //valor del token de autentificacion
         $token = $tokenArray;
         //valor del token de la sección
         $token_seccion = $params['token'];      
         //valor de la sección  
         $sessionId =$idSessionArray;
         //comprar si es correcto el token
        $authCheck = $jwt_auth->checkToken($token);

          if($authCheck){ 
                 $identity = $jwt_auth->checkToken($token, true);
                 $user_id=$identity->sub;
                 $usuario = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                    'id'=>$user_id
                ]);
                if($usuario){
                    foreach ($usuario->getWhallets() as $whallet) {
                     $saldo_whallet = $whallet->getSaldo();
                     $id_whallet=$whallet->getId();
                     $token_whallet = $whallet->getToken();
                     $whallet_session = $whallet->getSession();
                    }
                    //verificar que el token y la sessión exista 
                    if( $token_whallet == $token_seccion && $sessionId == $whallet_session ){
                        $whallet = $this->getDoctrine()->getRepository(Whallet::class)->findOneBy([
                            'id'=>$id_whallet
                        ]);
                        //verificar que la billetera asociada al cliente exista
                        if($whallet){
                            $id_session = $jwt_auth->checkTokenPayment($whallet_session);
                            if($id_session){
                            $pagar = $id_session->pagar;
                            $nuevo_saldo = $saldo_whallet-$pagar;
                            $em = $this->getDoctrine()->getManager(); 
                            $whallet->setToken('');
                            $whallet->setSession('');
                            $whallet->setSaldo($nuevo_saldo);
                            //guardar bd
                            $em->persist($whallet);
                            $em->flush();
                                 $data=[
                                'status' => 'success',
                                'code' => 200,
                                'message' => ' Pago realizado con exito '
                                ];
                            }else{
                                $data = [
                                    'status' => 'error',
                                    'code' => 400,
                                    'message' => 'token o sessión expirada!'
                                ];
                            }                  
                        }
                     
                       
                    }else{
                        $data = [
                            'status' => 'error',
                            'code' => 400,
                            'message' => 'token o sessión expirada!'
                        ];
                    }
                }
                 
         }else{
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Error, no se envio la información completa o  información incorrecta'
            ];
         }
               
        return $this->resjson($data);
    }

    public function consultar(Request $request, JwtAuth $jwt_auth ){
        //recoger los datos por post
  
         $params = json_decode($request->getContent(), true);
          //recojer el tocken
         $tokenArray= $params[0];
         $texto = preg_replace('([^A-Za-z0-9 ])', '', $tokenArray);
         $cadena_limpia = str_replace('"\"', '', $tokenArray);
         $token = $cadena_limpia;
         //comprar si es correcto el token
         $authCheck = $jwt_auth->checkToken($token);
         if($authCheck){ 
         //recoger el objeto del usuario identificado
         $identity = $jwt_auth->checkToken($token, true);
         //comprombar y validar datos
         if(!empty($params))
         {
             $user_id = ($identity->sub != null) ? $identity->sub : null;
             $documento = $params['documento'];
             $celular = $params['celular'];
             if(!empty($user_id)){
                $em = $this->getDoctrine()->getManager(); 
                $usuario = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                    'id'=>$user_id
                ]);
                if($documento && $celular && $usuario){
                    //conseguri los datos de la billetera asociado al cliente logueado
                    foreach ($usuario->getWhallets() as $whallet) {
                        $saldo_whallet = $whallet->getSaldo();
                        $id_whallet=$whallet->getId();
                        $user_whallet=$whallet->getUser();
                       }
                    
                       $em = $this->getDoctrine()->getManager(); 
                       $whalletR = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                           'id'=>$user_id
                       ]);
                          //verificar que tenga un registro de billetera
                       if($whalletR){
                       $algo = [$user_whallet];
                       $something2= json_encode($algo);
                       $data2 = json_decode($something2, true);
                       $user_whallet_id=$data2[0]['id'];
                       //validar que el id session y el id asociado a la billetra coincidan
                       $user_whallet_documento=$data2[0]['documento'];
                       $user_whallet_celular=$data2[0]['celular'];
                       if($celular ==  $user_whallet_celular &&  $documento ==  $user_whallet_documento){
                        $data = [
                            'status' => 'success',
                            'code' => 200,
                            'saldo'=>$saldo_whallet,
                        ];  
                       }
                     else{
                        $data = [
                            'status' => 'error',
                            'code' => 400,
                            'message' => 'informacion incorrecta'
                        ];  
                       }
                
                     
                       }else{
                        $data = [
                            'status' => 'error',
                            'code' => 400,
                            'message' => 'informacion incorrecta'
                        ];  
                       }

                      // print_r($id_user);
                      
                }else{
                    $data = [
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'la cedula o celular no se encuentran registrados! no'
                    ];
                }
             }
         }
         }else{
             $data = [
                 'status' => 'error',
                 'code' => 400,
                 'message' => 'authentificacion fallida!'
             ];
         }
         return $this->resjson($data);
     }

}
