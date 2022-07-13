<?php

namespace App\Controller;

use App\Entity\Course;
use App\Form\CourseType;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Service\BillingClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/courses')]
class CourseController extends AbstractController
{
    #[Route('/', name: 'app_course_index', methods: ['GET'])]
    public function index(CourseRepository $courseRepository, BillingClient $client): Response
    {
        $allCoursesFromBilling = $client->getAllCourses();
        $allCourses = $courseRepository->createQueryBuilder('c')
            ->getQuery()
            ->getArrayResult();
        $allCourses = $this->getFormatedArray($allCourses, 'code');
        $allCoursesFromBilling = $this->getFormatedArray($allCoursesFromBilling, 'code');
        if (!$this->getUser()) {
            $freeCourses = [];
            foreach ($allCourses as $code => $course) {
                if (!isset($allCoursesFromBilling[$code]) || $allCoursesFromBilling[$code]['type'] === 'free') {
                    $freeCourses[] = [
                        'course' => $course,
                        'accessInfo' => ['type' => 'free'],
                    ];
                }
            }
            return $this->render('course/index.html.twig', [
            'courses' => $freeCourses,
            ]);
        }

        $transactions = $client->getTransactions(
            ['type' => 'payment', 'skip_expired' => true],
            $this->getUser()->getApiToken()
        );
        $transactions = $this->getFormatedArray($transactions, 'code');
        $courses = [];
        foreach ($allCourses as $code => $course) {
            $courses[] = [
                'course' => $course,
                'accessInfo' => $allCoursesFromBilling[$code] ?? ['type' => 'free'],
                'transaction' => $transactions[$code] ?? null
            ];
        }
//        dd($courses);
        return $this->render('course/index.html.twig', [
            'courses' => $courses,
        ]);
    }

    public function getFormatedArray($array, $code) {
        $arrayOut = [];
        foreach ($array as $obj) {
            $arrayOut[$obj[$code]] = $obj;
        }
        return $arrayOut;
    }

    #[Route('/new', name: 'app_course_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CourseRepository $courseRepository): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($courseRepository->findOneBy(['code' => $course->getCode()])) {
                return $this->renderForm('course/new.html.twig', [
                    'course' => $course,
                    'form' => $form,
                ]);
            }
            $courseRepository->add($course);
            return $this->redirectToRoute('app_course_show', ['id' => $course->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('course/new.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_course_show', methods: ['GET'])]
    public function show(
        Course $course,
        LessonRepository $lessonRepository,
        BillingClient $client
    ): Response {
        $courseFromBilling = $client->getCourseByCode($course->getCode());
        $lessonsCourse = $lessonRepository->findBy(['course' => $course->getId()], ['number' => 'ASC']);
        $courseReturn = [];
        if (!$courseFromBilling || $courseFromBilling['type'] === 'free') {
            return $this->render('course/show.html.twig', [
                'course' => ['course' => $course, 'accessInfo' => $courseFromBilling ?? null, 'transaction' => null],
                'lessons' => $lessonsCourse,
            ]);
        }
        $transaction = $client->getTransactions(
            ['type' => 'payment', 'course_code' => $course->getCode(), 'skip_expired' => true],
            $this->getUser()->getApiToken()
        );
        if (!$transaction) {
            return $this->render('course/show.html.twig', [
                'course' => ['course' => $course, 'accessInfo' => $courseFromBilling, 'transaction' => null],
                'lessons' => $lessonsCourse,
            ]);
        }
//        dd($transaction);
        return $this->render('course/show.html.twig', [
            'course' => ['course' => $course, 'accessInfo' => $courseFromBilling, 'transaction' => $transaction[0]],
            'lessons' => $lessonsCourse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_course_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Course $course, CourseRepository $courseRepository): Response
    {
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $courseRepository->add($course);
            return $this->redirectToRoute('app_course_show', ['id' => $course->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('course/edit.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_course_delete', methods: ['POST'])]
    public function delete(Request $request, Course $course, CourseRepository $courseRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $course->getId(), $request->request->get('_token'))) {
            $courseRepository->remove($course);
        }

        return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/pay', name: 'app_course_pay', methods: ['GET'])]
    public function pay(
        Course $course,
        BillingClient $client
    ): Response
    {
        $res = $client->pay($course, $this->getUser()->getApiToken());
        if (isset($res['success'])) {
            $this->addFlash('notice', 'Оплата прошла успешно');
        } else {
            $this->addFlash('notice', 'Недостаточно средств');
        }

        return $this->redirectToRoute('app_course_show', ['id' => $course->getId()]);
    }
}
