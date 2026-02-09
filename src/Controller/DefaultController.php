<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use App\Entity\Section;

class DefaultController extends AbstractController
{
    

    public function index()
    {
        $em = $this->getDoctrine()->getManager();
    	
        $query = $em->createQuery(
            'SELECT s FROM App:Section s WHERE s.parent IS NULL ORDER BY s.sort DESC'
        )->setMaxResults(1000);
        $sections = $query->getResult();

        $symbols = 1;
        foreach ($sections as $section) {
            foreach ($section->getSections() as $subsection) {
                
                $subsection->pagesSum = ceil($symbols / Section::PER_PAGE);
                
                $symbols += $subsection->getSimbols();
                $subsection->simbolsSum = $symbols;

            }   
        }

        return $this->render('index.html.twig', array(
            'sections' => $sections
        ));
    }

   
}
