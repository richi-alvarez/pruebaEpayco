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
            $saldo = $params['saldo'];
            
            if(!empty($user_id)){
                $em = $this->getDoctrine()->getManager();
                $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                    'id'=>$user_id
                ]);
               
                $documento = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                    'documento'=>$documento
                ]);
                $celular = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                    'celular'=>$celular
                ]);
                if($documento && $celular){
                  
                $whallet = new Whallet();
                $whallet->setUser($user);
                $whallet->setSaldo($saldo);
                $whallet->setToken('');
                $createdAt = new \Datetime('now');
                $whallet->setCreatedAt($createdAt);
               // $whallet->setUpdatedAt($createdAt);

                //guardar bd
                $em->persist($whallet);
                $em->flush();

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'la recarga fue exitosa',
                    'saldo'=> $whallet
                ];
                }else{
                    $data = [
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'la cedula o celular no se encuentran registrados!'
                    ];
                }

               // var_dump('entro',$user);

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
