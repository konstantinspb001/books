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
  
    

    






}
