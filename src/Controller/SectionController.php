<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Section\Section;
use App\Entity\Section\SectionArchive;
use App\Entity\Section\SectionRecommendation;
use App\Service\Markdown;


use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Filesystem\Filesystem;
use DateTime;

class SectionController extends AbstractController
{
    
    //Страница раздела
    public function section($id, Markdown $markdownService)
    {
        $em = $this->getDoctrine()->getManager();
        $sectionRepository = $this->getDoctrine() ->getRepository(Section::class);
        $section = $sectionRepository ->findOneBy(['id' => $id]);
        $date = date('d.m.Y');
    	$markdown = '';
        $filesystem = new Filesystem();

        if(sizeof($section->getSections()) > 0) {
  
            foreach ($section->getSections() as $subSection) {
                $title = str_replace("\n", '', $subSection->getTitle());
                $markdown .= '#'.$title.'<a href="/section_red/'.$subSection->getId().'" style="font-size:21px;"> ✏️ </a>'."\n";

                //Один раз правим markdown
                /*
                $text = $subSection->getText();                
                $text = str_replace("\n", "\n\n", $text);
                $text = str_replace("|\n", "|", $text);
                $text = str_replace("\n\n\n", "\n\n", $text);   
                $subSection->setText($text);          
                $em->flush();
                /**/   

                $markdown .= $subSection->getText()."\n";
            }

        } else {
            $markdown = $section->getText();
 
        }

        $archives = $this->getDoctrine()->getRepository(SectionArchive::class) ->findBy(['section' => $id], ['id' => 'DESC'], 20);

        //Архиваня версия
        $archive = null;
        if(isset($_GET['archive_date'])) {
            $markdown = '';
            $archive = $this->getDoctrine()->getRepository(SectionArchive::class) ->findOneBy(
                ['section' => $id, 'date' => $_GET['archive_date']]
            );
            $filePath = $archive->getFile();
            $filesystem = new Filesystem();
            if (file_exists($filePath)) {
                $markdown = file_get_contents($filePath);
            } else {
                throw new \Exception('Файл не найден: ' . $filePath);
            }
        }

        //Идеи для раздела
        $ideas = $_GET['ideas'] ?? 0;
        $ideasLink = null;
        if($ideas) {
            $ideasLink = '/data/sections_ideas/'.$id . '.txt';
            $markdown = '';
            $sectionDir = $this->getParameter('kernel.project_dir') . '/public/data/sections_ideas';
            $filePath = $sectionDir . '/' . $id . '.txt';
            if ($filesystem->exists($filePath)) {
                $markdown = file_get_contents($filePath);
            } else {
                $filesystem->dumpFile($filePath, '');
                $markdown = '';
            }
             
        }

        $recommendations = $this->getDoctrine()->getRepository(SectionRecommendation::class)->findBy(
            ['section' => $id],
            ['id' => 'DESC']
        );
        foreach ($recommendations as $recommendation) {
            $recId = $recommendation->getId();
            if(!$recommendation->getDate()) {
                $recommendation->setDate($date);
                $em->flush();
            };
            $link = '/data/sections_recommendations/'.$id . '.txt';
            $recomendDir = $this->getParameter('kernel.project_dir') . '/public/data/sections_recommendations';
            $filePath = $recomendDir . '/' . $recId . '.txt';
            if (!$filesystem->exists($filePath)) {
                $filesystem->dumpFile($filePath, '');
            };
        }


        $html = $markdownService->toHtml($markdown);   
        return $this->render('section.html.twig', array(
            'section' => $section,
            'html' => $html,
            'archives' => $archives,
            'archive' => $archive,
            'ideasLink' => $ideasLink,
            'recommendations' => $recommendations,
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


        //правим
        $content = str_replace(' — ', ' - ', $content);

        
        $sectionRepository = $this->getDoctrine() ->getRepository(Section::class);
        $section = $sectionRepository ->findOneBy(['id' => $id]);
        if($section) {
            $section->setText($content);
            $section->setChangetAt(new DateTime());
            $em->flush();
        } else {
            $status = 'error';
        }

        //Сохраняем версию
        $date = date('d.m.Y');
        $sectionArchiveRepository = $this->getDoctrine() ->getRepository(SectionArchive::class);
        $sectionArchive = $sectionArchiveRepository ->findOneBy(['section' => $id, 'date' => $date]);
        if(!$sectionArchive) {
            $sectionArchive = new SectionArchive();
            $sectionArchive -> setSection($section);
            $sectionArchive -> setDate($date);
            $em->persist($sectionArchive);
            $em->flush();      

        }

        $filesystem = new Filesystem();
        $baseDir = $this->getParameter('kernel.project_dir') . '/public/data/sections_archive';
        $sectionDir = $baseDir . '/' . $id;
        $filePath = $sectionDir . '/' . $date . '.txt';

        // Создаст все вложенные папки автоматически
        $filesystem->dumpFile($filePath, $content);      
        $sectionArchive -> setFile('data/sections_archive/'.$id . '/' . $date . '.txt');
        $em->flush(); 

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
