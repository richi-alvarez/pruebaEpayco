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
        $user_repo = $this->getDoctrine()->getRepository(User::class);
        $whallet_repo = $this->getDoctrine()->getRepository(Whallet::class);

        $users = $user_repo->findAll();
        $user = $user_repo->find(1);
        $data = [
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ];
    

        return $this->resjson ($users);
    }

    public function registro(Request $request){
        //recojer los datos por post
        $json = $request->get('json', null);
        //decodificar el json
        $params = json_decode($json);

        //hacer una respuesta

        $data = [
            'status' => 'success',
            'code' => 200,
            'message' => 'Se guardaron los datos con exito',
            'user' => $params,
            // 'billetera' => $billetera
        ];

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

            if(!empty($email) && count($validate_email) ==0 && !empty($name) && !empty($documento) && !empty($celular)  && !empty($password)){
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

        //si no existe guardar en bd

        //hacer respuesta en json
                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Se guardaron los datos con exito',
                    'user' => $user
                ];

            }else{
                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'email incorrecto'
                ];
            }

            
        }else{
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'no se enviaron datos o datos incorrectos'
            ];
        }

        return new JsonResponse($data);

    }
}
