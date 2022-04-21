<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Lesson;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CourseFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        //Курс Python Basic
        $coursePython = new Course();
        $coursePython->setCode('uid1');
        $coursePython->setName('Python Basic');
        $coursePython->setDescription('На Python создают веб-приложения и нейросети, проводят научные вычисления и автоматизируют процессы. Вы научитесь программировать на востребованном языке с нуля, напишете Telegram-бота для турагентства и сможете начать карьеру в разработке.');
        //Добавление уроков
        $lesson = new Lesson();
        $lesson->setName('Введение');
        $lesson->setContent('Научитесь работать с онлайн-редактором кода. Напишете первую программу. Освоите работу с функцией print.');
        $lesson->setNumber(1);
        $coursePython->addLesson($lesson);
        $lesson = new Lesson();
        $lesson->setName('Основы работы с Python');
        $lesson->setContent('Изучите работу с переменными, оператором ввода input и строками.');
        $lesson->setNumber(2);
        $coursePython->addLesson($lesson);
        $lesson = new Lesson();
        $lesson->setName('Операторы, выражения');
        $lesson->setContent('Изучите арифметические операции с числами, порядок их выполнения, ввод чисел с клавиатуры, деление нацело и с остатком, а также сокращённые операторы.');
        $lesson->setNumber(3);
        $coursePython->addLesson($lesson);
        $manager->persist($coursePython);
        //Java-разработчик
        $courseJava = new Course();
        $courseJava->setCode('uid2');
        $courseJava->setName('Java-разработчик');
        $courseJava->setDescription('Вы научитесь писать код и создавать сайты на самом популярном языке программирования. Разработаете блог, добавите сильный проект в портфолио и станете Java-программистом, которому рады в любой студии разработки.');
        //Добавление уроков
        $lesson = new Lesson();
        $lesson->setName('Вводный модуль');
        $lesson->setContent('Вы узнаете, где применяется язык Java и как выглядит программный код. Установите среду разработки и напишете первое консольное приложение.');
        $lesson->setNumber(1);
        $courseJava->addLesson($lesson);
        $lesson = new Lesson();
        $lesson->setName('Синтаксис языка');
        $lesson->setContent('Познакомитесь с основными переменными в языке Java, научитесь использовать операторы сравнения и циклы.');
        $lesson->setNumber(2);
        $courseJava->addLesson($lesson);
        $lesson = new Lesson();
        $lesson->setName('Система контроля версий Git');
        $lesson->setContent('Научитесь работать с Git: сможете сравнивать, менять и откатывать разные версии кода, научитесь создавать ветки и работать над одним проектом в команде.');
        $lesson->setNumber(3);
        $courseJava->addLesson($lesson);
        $lesson = new Lesson();
        $lesson->setName('Объекты и классы. Часть 1. Методы и классы');
        $lesson->setContent('Узнаете, что такое объекты, классы и методы в Java. Поймёте, как они работают, научитесь создавать их и использовать.');
        $lesson->setNumber(4);
        $courseJava->addLesson($lesson);
        $manager->persist($courseJava);
        //1С-разработчик
        $course1C = new Course();
        $course1C->setCode('uid3');
        $course1C->setName('1С-разработчик');
        $course1C->setDescription('Станьте разработчиком в системе 1С:Предприятие под руководством личного наставника и зарабатывайте на этом даже без опыта программирования. После прохождения курса – помощь в трудоустройстве.');
        //Добавление уроков
        $lesson = new Lesson();
        $lesson->setName('1С-разработчик с нуля до PRO');
        $lesson->setContent('Обзор системы «1С:Предприятие».Подсистемы и справочные объекты.Документы.Регистры.Отчёты.');
        $lesson->setNumber(1);
        $course1C->addLesson($lesson);
        $lesson = new Lesson();
        $lesson->setName('Мини-курс «Язык запросов SQL»');
        $lesson->setContent('SQL. Введение.');
        $lesson->setNumber(2);
        $course1C->addLesson($lesson);
        $lesson = new Lesson();
        $lesson->setName('Курс «Универсальные знания программиста»');
        $lesson->setContent('Как стать первоклассным программистом.');
        $lesson->setNumber(3);
        $course1C->addLesson($lesson);
        $manager->persist($course1C);
        $manager->flush();
    }
}
