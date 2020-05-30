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
        //recojer el tocken
        $params = json_decode($request->getContent(), true);
        
        $tokenArray=array_values($params);
        $texto = preg_replace('([^A-Za-z0-9 ])', '', $tokenArray[1]);
        $cadena_limpia = str_replace('"\"', '', $tokenArray[1]);
        $token = $cadena_limpia;
        var_dump($tokenArray);
        //comprar si es correcto el token
        $authCheck = $jwt_auth->checkToken($token);
        if($authCheck){
        //recojer datos por post
            
            $data = [
                'status' => 'success',
                'code' => 200,
                'message' => 'el token es correcto'
            ];

        }else{
            $data = [
                'status' => 'error',
                'code' => 200,
                'message' => 'authentificacion fallida!'
            ];
        }
        return $this->resjson($data);
    }
}
