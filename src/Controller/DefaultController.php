<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use App\Entity\Section\Section;

class DefaultController extends AbstractController
{
    

    public function index()
    {
        $em = $this->getDoctrine()->getManager();
    	$action = $_GET['action'] ?? null;   

        $query = $em->createQuery(
            'SELECT s FROM App\Entity\Section\Section s WHERE s.parent IS NULL ORDER BY s.sort DESC'
        )->setMaxResults(1000);
        $sections = $query->getResult();


        $json = '{
  "Введение": "introduction",
  "О книге": "about_book",
  "Устройство сетевой диаспоры": "network_diaspora_structure",
  "Диаспора и этническая общность": "diaspora_and_ethnic_community",
  "Зачем нужна сетевая диаспора ?": "why_network_diaspora_is_needed",
  "Мировые тенденции": "global_trends",
  "Конкуренция этносов": "ethnic_competition",
  "Специализация и высокие требования к профессионализму": "specialization_and_professional_standards",
  "Снижение роли государства": "declining_role_of_the_state",
  "Этнос вне государства": "ethnos_beyond_the_state",
  "Сеть сообществ": "community_network",
  "Сообщество в сетевой диаспоре": "community_in_network_diaspora",
  "Gathering Place": "gathering_place",
  "Сообщество как элемент децентрализованной системы": "community_as_a_decentralized_element",
  "Самофинансирование": "self_financing",
  "Администратор сообщества": "community_administrator",
  "Участники сообществ": "community_members",
  "О лидерах и вождях": "leaders_and_chiefs",
  "Евангелисты": "evangelists",
  "Кооперация": "cooperation",
  "Мобильность": "mobility",
  "Онлайн сервис сообщества": "community_online_service",
  "Информационная среда диаспоры": "diaspora_information_environment",
  "Децентрализованный веб": "decentralized_web",
  "Пример архитектуры сети": "network_architecture_example",
  "Почему это реализуемо только в диаспоре": "why_this_is_only_feasible_in_diaspora",
  "Веб данных как среда сетевой диаспоры": "web_of_data_for_network_diaspora",
  "Онтологии сетевой диаспоры": "network_diaspora_ontologies",
  "Элементы информационной системы": "information_system_elements",
  "Принципы информационной системы": "information_system_principles",
  "Блокчейн и токенизация": "blockchain_and_tokenization",
  "Токен сетевой диаспоры": "network_diaspora_token",
  "Токенизация прав и активов": "tokenization_of_rights_and_assets",
  "Смарт-контракты": "smart_contracts",
  "Открытые данные": "open_data",
  "Что такое открытые данные": "what_are_open_data",
  "Внешняя память диаспоры": "diaspora_external_memory",
  "Агрегация и анализ данных": "data_aggregation_and_analysis",
  "Система социального рейтинга": "social_rating_system",
  "Доверие и социальный капитал": "trust_and_social_capital",
  "Искусственный интеллект": "artificial_intelligence",
  "Данные и промпты - достояние диаспоры": "data_and_prompts_as_common_heritage",
  "Вайб-кодинг": "vibe_coding",
  "ИИ как управленец и судья": "ai_as_manager_and_judge",
  "AI-native общество": "ai_native_society",
  "Экономика": "economics",
  "Этнические олигополии": "ethnic_oligopolies",
  "Сеть предприятий и аутсорсинг": "business_network_and_outsourcing",
  "Сообщества и бизнес": "communities_and_business",
  "Диаспора и государства": "diaspora_and_states",
  "Коллективная собственность": "collective_ownership",
  "Локальные деньги": "local_money",
  "Открытые пожертвования": "open_donations",
  "Роль капитала": "role_of_capital",
  "Семья": "family",
  "О демографии": "about_demography",
  "Мужчины и женщины": "men_and_women",
  "Формирование пар в диаспоре": "pair_formation_in_diaspora",
  "Семейные клубы и воспитание детей": "family_clubs_and_child_rearing",
  "Расширенная семья": "extended_family",
  "Элементы культуры и быта": "culture_and_daily_life_elements",
  "Формирование культуры в сообществах": "culture_formation_in_communities",
  "Субэтносы сетевой диаспоры": "subethnic_groups_of_network_diaspora",
  "Заимствование элементов из других культур": "borrowing_from_other_cultures",
  "Проблемы массовой культуры": "problems_of_mass_culture",
  "Научный подход к формированию культуры": "scientific_approach_to_culture_formation",
  "Книги как важный аспект культуры": "books_as_an_important_part_of_culture",
  "Эволюционный механизм": "evolutionary_mechanism",
  "Современные социальные инструменты": "modern_social_tools",
  "Умный этнос": "smart_ethnos",
  "Фреймворк жизни": "life_framework",
  "Аутсорсинг жизни": "life_outsourcing",
  "💩 Система внутреннего образования": "internal_education_system",
  "💩 Культура потребления": "consumer_culture",
  "\"Монахи\" и бездельники": "monks_and_idlers",
  "Заимствуем у религий": "borrowing_from_religions",
  "Мягкая сила": "soft_power",
  "Чувство превосходства": "sense_of_superiority",
  "Аборигены": "natives",
  "Меняем мир": "changing_the_world",
  "Привилегии вместо гражданства": "privileges_instead_of_citizenship",
  "Сетевая диаспора как класс управленцев": "network_diaspora_as_a_ruling_class",
  "Генетическая стратегия": "genetic_strategy",
  "Класс пассионариев диаспоры": "diaspora_passionary_class",
  "Мы сделаем мир лучше": "we_will_make_the_world_better",
  "Заключение": "conclusion",
  "Действуй": "act_now"
}';
        $urls = json_decode($json, 1);


        $symbols = 1;
        foreach ($sections as $section) {

            //echo $section->getTitle()."<br/>";

            foreach ($section->getSections() as $subsection) {
                    
                $url = $urls[$subsection->getTitle()] ?? null;
                //echo $subsection->getTitle()." -> $url<br/>";

                if($url && !$subsection->getUrl()) {
                    $subsection->setUrl($url);
                }


                if($subsection->getStatus() == Section::STATYS_ARCHIVE) continue;

                $subsection->pagesSum = ceil($symbols / Section::PER_PAGE);
                
                $symbols += $subsection->getSimbols();
                $subsection->simbolsSum = $symbols;

            }   
        }
        $em->flush();
        //die();

        //Вывод всей книги
        if($action == 'book') {
            $book = '#Сетевая диаспора'."\n";
            foreach ($sections as $section) {
                $book .= '##'.$section->getTitle()."\n";
                foreach ($section->getSections() as $subsection) {
                    if($subsection->getStatus() == 'archive') continue;
                    $book .= '###'.$subsection->getTitle()."\n";
                    $book .= $subsection->getText()."\n"; 

                }   
            }; 
            echo '<textarea style="width:100%; height:100%;">'.$book.'</textarea>'; die();           
        };

        //Книга в html
        if($action == 'pdf') {   

try {
    $coverPath = __DIR__ . '/../../public/book.png';
    $coverPath = str_replace('\\', '/', realpath($coverPath) ?: $coverPath);

    if (!file_exists($coverPath)) {
        throw new Exception("Обложка не найдена: " . $coverPath);
    }

    $mpdf = new \Mpdf\Mpdf([
        'default_font' => 'DejaVuSans',
    ]);

    $mpdf->SetAnchor2Bookmark(1);

    // === ОБЛОЖКА (страница 1) ===
    $mpdf->SetHTMLHeader('');
    $mpdf->SetHTMLFooter('');

    $mpdf->AddPageByArray([
        'orientation' => 'P',
        'margin-left' => 0,
        'margin-right' => 0,
        'margin-top' => 0,
        'margin-bottom' => 0,
    ]);

    // Растягиваем PNG на весь A4
    $mpdf->Image($coverPath, 0, 0, 210, 297, 'png', '', true, false);

    // === ОГЛАВЛЕНИЕ (страница 2) ===
    // Новая страница с полями, колонтитулы включаем
    $mpdf->AddPageByArray([
        'orientation' => 'P',
        'margin-left' => 15,
        'margin-right' => 15,
        'margin-top' => 25,
        'margin-bottom' => 25,
    ]);

    $mpdf->SetHTMLHeader('
        <div style="text-align: center; font-size: 10pt; color: #666; border-bottom: 0.5px solid #999; padding-bottom: 5px;">
            Моя книга
        </div>
    ');
    $mpdf->SetHTMLFooter('
        <div style="text-align: center; font-size: 10pt; color: #666;">
            Страница {PAGENO} из {nbpg}
        </div>
    ');

    // Убрано page-break-before: always из h1!
    // Разрывы только через <pagebreak> перед главами
    $html = '
    <style>
        h1 { color: #2c3e50; }
        a { color: #2980b9; text-decoration: none; }
    </style>

    <h1><a name="toc"></a>Оглавление</h1>
    <p><a href="#chapter1">Глава 1. Введение</a></p>
    <p><a href="#chapter2">Глава 2. Основная часть</a></p>
    <p><a href="#chapter3">Глава 3. Заключение</a></p>

    <pagebreak />

    <h1><a name="chapter1"></a>Глава 1. Введение</h1>
    <p>Текст первой главы.</p>
    <p><a href="#toc">Вернуться к оглавлению</a></p>
    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>

    <pagebreak />

    <h1><a name="chapter2"></a>Глава 2. Основная часть</h1>
    <p>Основное содержание книги.</p>
    <p><a href="#toc">В оглавление</a></p>
    <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>

    <pagebreak />

    <h1><a name="chapter3"></a>Глава 3. Заключение</h1>
    <p>Финальная глава с выводами.</p>
    <p><a href="#toc">В оглавление</a></p>
    <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium.</p>
    ';

    $mpdf->WriteHTML($html);

    // Сохранение
    $outputDir = __DIR__ . '/../../public/output';
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0775, true);
    }

    $outputPath = $outputDir . '/book.pdf';
    $mpdf->Output($outputPath, 'F');

    echo "✅ PDF создан: " . realpath($outputPath) . "<br>";
    echo "📊 Размер: " . round(filesize($outputPath) / 1024, 2) . " КБ<br>";
    echo "<a href='/output/book.pdf' target='_blank'>Открыть PDF</a>";

} catch (\Throwable $e) {
    echo "❌ Ошибка: " . $e->getMessage();
}
           die();
        }

        //Саммари
        if($action == 'sammari') {
            $sammari = file_get_contents('sammari.txt');
            echo '<textarea style="width:100%; height:100%;">'.$sammari.'</textarea>'; die();           
        };        
        //markdown
        if(isset($_GET['markdown'])) {
            $id = $_GET['markdown'];
            $section = $this->getDoctrine()->getRepository(Section::class)->findOneBy(['id' => $id]);
            $markdown = '#'.$section->getTitle()."\n";
            foreach ($section->getSections() as $subsection) {
                $markdown .= '##'.$subsection->getTitle()."\n";
                $markdown .= $subsection->getText()."\n"; 
            }   
       
            echo '<textarea style="width:100%; height:100%;">'.$markdown.'</textarea>'; die();           
        }; 



        return $this->render('index.html.twig', array(
            'sections' => $sections
        ));
    }

   
}
