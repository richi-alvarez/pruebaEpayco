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

class UserController extends AbstractController
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
        $data = [
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ];

        return $this->resjson ($data);
    }

    public function registro(Request $request){
        //recojer los datos por post
        $json = $request->get('json', null);
        //decodificar el json
        $params = json_decode($json);
        //comprobar y valdiar datos
        if($json != null){
            $name = (!empty($params->name)) ? $params->name: null;
            $documento = (!empty($params->documento)) ? $params->documento: null;
            $email = (!empty($params->email)) ? $params->email: null;
            $celular = (!empty($params->celular)) ? $params->celular: null;
            $password = (!empty($params->password)) ? $params->password: null;

            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email, [
                new Email()
            ]);

        if(!empty($email) && count($validate_email) ==0 
            && !empty($name) && !empty($documento) 
            && !empty($celular)  && !empty($password))
        {
        //si la validación es correcta, crear el objeto de usuario
            $user = new User();
            $user->setName($name);
            $user->setDocumento($documento);
            $user->setEmail($email);
            $user->setCelular($celular);
            $user->setCreatedAt(new \Datetime('now'));
            //sifrar contraseña
            $pwd = hash('sha256',$password);
            $user->setPassword($pwd);
            //comprobar si existe el usuario
            $doctrine=$this->getDoctrine();
            $em =$doctrine->getManager();

            $user_repo = $doctrine->getRepository(User::class);
            $isset_user = $user_repo->findBy(array(
                'email'=>$email
            ));
            //si no existe guardar en bd
                if(count($isset_user)==0){
                    //guardar usuario
                    $em->persist($user);
                    $em->flush();
                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Se guardaron los datos con exito',
                        'user' => $user
                    ];

                }else{
                    $data = [
                        'status' => 'error',
                        'code' => 200,
                        'message' => 'El usuario ya existe'
                    ];
                }
          
        }else{
                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'no se enviaron datos o datos incorrectos'
                ];
            }

            
        }else{
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'no se enviaron datos o datos incorrectos'
            ];
        }

        return $this->resjson($data);
    }

    public function login(Request $request){
        //recibir datos por post
        $json = $request->get('json', null);
         //convertir a objeto
         $params = json_decode($json);
        //comporvar y validar datos
        if($json != null){
            $email = (!empty($params->email)) ? $params->email: null;
            $password = (!empty($params->password)) ? $params->password: null;
            $gettoken = (!empty($params->gettoken)) ? $params->gettoken: null;
            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email, [
                new Email()
            ]);

            if(!empty($email) && count($validate_email) ==0  && !empty($password))
            {
            //cifrar contraseña
            $pwd = hash('sha256',$password);
            //si todo es valido, llamar servicio de autentificacion jwt, token

            //crear servicio jwt
            $data = [
                'status' => 'success',
                'code' => 200,
                'message' => 'El usuario no se ha podido identificar'
                    ];
            }else{
                $data = [
                    'status' => 'error',
                    'code' => 200,
                    'message' => 'El usuario no se ha podido identificar'
                ];
            }

            }
     
        //si todo es ok, responder
        return $this->resjson($data);
    }
}
