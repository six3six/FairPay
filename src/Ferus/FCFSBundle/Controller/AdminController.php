<?php

namespace Ferus\FCFSBundle\Controller;

use Ferus\FCFSBundle\Entity\Event;
use Ferus\FCFSBundle\Form\EventType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Braincrafted\Bundle\BootstrapBundle\Session\FlashMessage;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class AdminController extends Controller
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var FlashMessage
     */
    private $flash;

    /**
     * @Template
     */
    public function indexAction()
    {
        return array(
            'events' => $this->em->getRepository('FerusFCFSBundle:Event')->findAll(),
        );
    }

    /**
     * @Template
     */
    public function addAction(Request $request)
    {
        $event = new Event;

        return $this->handleForm($event, $request);
    }

    private function handleForm(Event $event, Request $request)
    {
        $form = $this->createForm(new EventType, $event);

        if($request->isMethod('POST')){
            $form->handleRequest($request);

            if($form->isValid()){
                $this->em->persist($event);
                $this->em->flush();
                $this->flash->success('K\'fet d\'élection enregistrée');

                return $this->redirect($this->generateUrl('fcfs_admin_index'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Template
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function editAction(Event $event, Request $request)
    {
        $form = $this->createForm(new EventType, $event);

        if($request->isMethod('POST')){
            $form->handleRequest($request);

            if($form->isValid()){
                $this->em->persist($event);
                $this->em->flush();

                $this->flash->success('K\'fet d\'élection mise à jour');
                return $this->redirect($this->generateUrl('fcfs_admin_index'));
            }
        }

        return array(
            'event' => $event,
            'form' => $form->createView(),
        );
    }

    /**
     * @Template
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function removeAction(Event $event, Request $request)
    {
        if($request->isMethod('POST')){

            $this->em->remove($event);
            $this->em->flush();

            $this->flash->success('K\'fet d\'élection supprimé.');

            return $this->redirect($this->generateUrl('fcfs_admin_index'));
        }

        return array(
            'event' => $event,
        );
    }

    public function downloadAction(Event $event)
    {
        $data = $this->em->getRepository('FerusFCFSBundle:EventRegistration')->findData($event);

        $csv = implode(';', array('id', 'Nom', 'Email', 'Date'))."\n".implode("\n", array_map(function($t){
                return $t['id'].';'.$t['name'].';'.$t['email'].';'.$t['createdAt']->format('d/m/Y H:i');
            }, $data));

        $response = new Response();
        $response->setContent($csv);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('charset', 'UTF-8');
        $filename = $event;
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'.csv"');
        return $response;
    }

    public function sendWarnEmailAction(Event $event)
    {
        $kernel = $this->get('kernel');
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $input = new ArrayInput(array(
           'command' => 'fcfs:warn:send-email',
           'event' => $event->getId()
        ));
        $output = new BufferedOutput();
        $application->run($input, $output);
        $content = $output->fetch();
        return new Response($content);
    }

    public function sendRegistrationEmailAction(Event $event)
    {
        $kernel = $this->get('kernel');
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $input = new ArrayInput(array(
           'command' => 'fcfs:registration:send-email',
           'event' => $event->getId()
        ));
        $output = new BufferedOutput();
        $application->run($input, $output);
        $content = $output->fetch();
        return new Response($content);
    }
}
