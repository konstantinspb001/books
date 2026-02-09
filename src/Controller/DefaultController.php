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
    	$action = $_GET['action'] ?? null;   

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

        //Вывод всей книги
        if($action == 'book') {
            $book = '#Сетевая диаспора'."\n";
            foreach ($sections as $section) {
                $book .= '##'.$section->getTitle()."\n";
                foreach ($section->getSections() as $subsection) {
                    $book .= '###'.$subsection->getTitle()."\n";
                    $book .= $subsection->getText()."\n"; 

                }   
            }; 
            echo '<textarea style="width:100%; height:100%;">'.$book.'</textarea>'; die();           
        };

        //Саммари
        if($action == 'sammari') {
            $sammari = file_get_contents('sammari.txt');
            echo '<textarea style="width:100%; height:100%;">'.$sammari.'</textarea>'; die();           
        };        




        return $this->render('index.html.twig', array(
            'sections' => $sections
        ));
    }

   
}
