<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Section;
use App\Service\Markdown;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class SectionController extends AbstractController
{
    

    public function section($id, Markdown $markdownService)
    {

        $sectionRepository = $this->getDoctrine() ->getRepository(Section::class);
        $section = $sectionRepository ->findOneBy(['id' => $id]);
    	
        $html = $markdownService->toHtml($section->getText());

        return $this->render('section.html.twig', array(
            'section' => $section,
            'html' => $html,
        ));



    }

    
    public function edite($id)
    {

        $sectionRepository = $this->getDoctrine() ->getRepository(Section::class);
        $section = $sectionRepository ->findOneBy(['id' => $id]);
        

        return $this->render('edite.html.twig', array(
            'section' => $section
        ));



    }
  
    
    //Принимаем запрос с фронта
    public function save()
    {
        $em = $this->getDoctrine()->getManager();
        $id = 0;
        $status = 'success';

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $content = $data['content'];
        $idText = $data['id'] ?? '';
        $timestamp = $data['timestamp']; 

        $idArray = explode('_', $idText);
        if(sizeof($idArray) == 2 && $idArray[0] = 'section') {
            $id = $idArray[1];
        };

        
        $sectionRepository = $this->getDoctrine() ->getRepository(Section::class);
        $section = $sectionRepository ->findOneBy(['id' => $id]);
        if($section) {
            $section->setText($content);
            $em->flush();
        } else {
            $status = 'error';
        }

        $result = [
            'status' => $status,
            'message' => 'Сохранено',
            'id' => $id,
            'add_symbols' => 100
        ];

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        die();

    }
     






}
