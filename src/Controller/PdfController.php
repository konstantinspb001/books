<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Section\Section;
use App\Service\Markdown;

class PdfController extends AbstractController
{
    public function index(Markdown $markdownService)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT s FROM App\Entity\Section\Section s WHERE s.parent IS NULL ORDER BY s.sort DESC'
        )->setMaxResults(1000);
        $sections = $query->getResult();

        try {
            $coverPath = __DIR__ . '/../../public/book.jpg';
            $coverPath = str_replace('\\', '/', realpath($coverPath) ?: $coverPath);

            $mpdf = new \Mpdf\Mpdf([
                'default_font' => 'Georgia',
                'margin_left' => 25,
                'margin_right' => 25,
                'margin_top' => 45,
                'margin_bottom' => 35,

            ]);

            $mpdf->SetAuthor('diaspora.su');
            $mpdf->SetTitle('Сетевая диаспора');
            $mpdf->SetSubject('diaspora.su');
            $mpdf->SetKeywords('diaspora.su, Сетевая диаспора, copyright '.date('Y'));
            $mpdf->SetCreator('diaspora.su');

            // === ОБЛОЖКА ===
            $mpdf->AddPageByArray([
                'orientation' => 'P',
                'margin-left' => 0, 'margin-right' => 0,
                'margin-top' => 0, 'margin-bottom' => 0,
                'odd-header-value' => -1, 'even-header-value' => -1,
                'odd-footer-value' => -1, 'even-footer-value' => -1,
            ]);

            if (file_exists($coverPath)) {
                $mpdf->Image($coverPath, 0, 0, 210, 297, 'jpg', '', true, false);
            } else {
                $mpdf->WriteHTML('<div style="text-align:center;padding-top:120px;"><h1>Обложка не найдена</h1></div>');
            }

            $mpdf->AddPageByArray([
                'margin-left' => 10, 'margin-right' => 10,
                'margin-top' => 15, 'margin-bottom' => 15,
            ]);

            // === ОБЩИЕ СТИЛИ КОНТЕНТА ===
            $mpdf->WriteHTML('
            <style>
                h1 { color: #2c3e50; font-size: 21pt; margin-top: 10px; margin-bottom: 15px; }
                h2 { color: #34495e; font-size: 16pt; margin-top: 15px; margin-bottom: 10px; }
                p  { line-height: 1.5; font-size: 12pt; 
                        page-break-inside: avoid;  /* Не разрывать абзац посередине */
                        orphans: 3;                /* Минимум 3 строки в начале страницы */
                        widows: 3;                 /* Минимум 3 строки в конце страницы */
                }
            </style>
            ');

            /*
            $mpdf->WriteHTML('
                <div style="text-align:center; padding-top:250px; font-size:11pt; color:#444; line-height:1.6;">
                    © diaspora.su, '.date('Y').'<br>
                    Все права защищены.<br>
                    Никакая часть данного издания не может быть воспроизведена<br>
                    без разрешения правообладателя.<br><br>
                    Сайт: diaspora.su<br>
                    Версия документа: v1.0 · '.date('d-m-Y').'
                </div>
            ');*/
            $mpdf->WriteHTML('
                <div style="text-align:center; padding-top:250px; font-size:11pt; color:#444; line-height:1.6;">
                    © diaspora.su, '.date('Y').'
                    <br><br>
                    Эта работа распространяется по лицензии<br>
                    Creative Commons Attribution-NonCommercial-NoDerivatives 4.0<br>
                    (CC BY-NC-ND 4.0)<br>
                    <br>
                    Вы можете свободно копировать и распространять данный материал<br>
                    в любом формате при условии указания автора/источника, без изменений<br>
                    и без использования в коммерческих целях.<br>
                    <br>
                    Сайт: diaspora.su<br>
                    Версия документа: v1.0 · '.date('d-m-Y').'
                </div>
            ');
            //Подробнее: creativecommons.org/licenses/by-nc-nd/4.0<br><br>
                    


            // === ОГЛАВЛЕНИЕ ===
            $mpdf->TOCpagebreakByArray([
                'toc-prehtml'           => '<h1 style="text-align:center;margin-bottom:30px;font-weight:normal;color:#2c3e50;">Оглавление</h1>',
                'toc-bookmarkText'      => 'Оглавление',
                'resetpagenum'          => 1,
                'links'                 => 'on',
                'toc-odd-header-value'  => -1,
                'toc-even-header-value' => -1,
                'toc-odd-footer-value'  => -1,
                'toc-even-footer-value' => -1,
            ]);

            // Колонтитулы
            $mpdf->SetHTMLHeader('
                <div style="text-align:left;font-size:9pt;color:#666;border-bottom:0.5px solid #ccc;padding-bottom:4px;">
                    Сетевая диаспора
                </div>
            ');
            $mpdf->SetHTMLFooter('
                <div style="text-align:right;font-size:9pt;color:#666;">{PAGENO}</div>
            ');


            // === КОНТЕНТ ===
            foreach ($sections as $i => $section) {

                if(!$section->isActive()) continue;
                
                if ($i > 0) {
                    $mpdf->AddPage();
                }

                $markdown = $section->getText();
                $html = $markdownService->toHtml($markdown);

                //стиль для оглавления
                $titleStyled = '<span style="font-weight: normal;font-size: 14pt; ">' . $section->getTitle() . '</span>';
                $mpdf->TOC_Entry($titleStyled, 0);
                
                $mpdf->WriteHTML('<h1>' . $section->getTitle() . '</h1>');
                $mpdf->WriteHTML($html);

                foreach ($section->getSections() as $subSection) {

                    if(!$subSection->isActive()) continue;

                    $markdown = $subSection->getText();
                    $html = $markdownService->toHtml($markdown);

                    //стиль для оглавления
                    $subTitleStyled = '<span style="font-weight: normal; font-size: 12pt; font-style: normal; line-height: 1.5;">' . $subSection->getTitle() . '</span>';
                    $mpdf->TOC_Entry($subTitleStyled, 1);

                    $mpdf->WriteHTML('<h2 style="page-break-before: avoid;">' . $subSection->getTitle() . '</h2>');
                    $mpdf->WriteHTML($html);
                }
            }

            // Сохранение
            $outputDir = __DIR__ . '/../../public/output';
            if (!is_dir($outputDir)) mkdir($outputDir, 0775, true);

            $outputPath = $outputDir . '/book.pdf';
            $mpdf->Output($outputPath, 'F');

            echo "<div style='font-family:Arial,sans-serif;padding:20px;background:#f5f5f5;border:1px solid #ddd;'>";
            echo "<h2 style='color:green;margin-top:0;'>✅ PDF создан</h2>";
            echo "<p><b>Путь:</b> " . realpath($outputPath) . "</p>";
            echo "<p><b>Размер:</b> " . round(filesize($outputPath) / 1024, 2) . " КБ</p>";
            echo "<a href='/output/book.pdf' target='_blank' style='display:inline-block;padding:10px 20px;background:#2980b9;color:#fff;text-decoration:none;border-radius:3px;'>➤ Открыть PDF</a>";
            echo "</div>";

        } catch (\Throwable $e) {
            echo "❌ Ошибка: " . $e->getMessage();
        }
        die();
    }
}