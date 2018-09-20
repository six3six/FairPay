<?php


namespace Ferus\StudentBundle\Import;

use Doctrine\ORM\EntityManager;
use Ferus\StudentBundle\Entity\Student;

class ImportCsv 
{
    /**
     * @var EntityManager
     */
    private $em;

    private $success = 0;
    private $error = 0;

    function __construct($em)
    {
        $this->em = $em;
    }

    public function import($csv)
    {
        #The softdelete function can be a trouble while looking for available ID
        $this->em->getFilters()->disable('softdeleteable');

        $csv = preg_replace('#[\n\r]+#', '#', $csv);

        $lines = explode('#', $csv);

        $ids = array();

        foreach($lines as $line){
            $info = explode(';', $line);
            $student = new Student();
            $student->setId($info[0]);
            $student->setLastName($info[1]);
            $student->setFirstName($info[2]);
            if(isset($info[3]) && $info[3] != '') $student->setClass(preg_replace('#^[0-9]+_(.+)$#', '$1', $info[3]));
            if(isset($info[4]) && $info[4] != '') $student->setEmail($info[4]);
            #$student->setIsContributor(false);

            if($this->em->getRepository('FerusStudentBundle:Student')->isIdAvailable($student->getId())){
                $this->em->persist($student);
                $this->em->flush();

                $this->success++;
            }
            else{
                $student = $this->em->getRepository('FerusStudentBundle:Student')->findOneById($student->getId());
                $student->setLastName($info[1]);
                $student->setFirstName($info[2]);
                $student->setEmail(null);
                $student->setDeletedAt(null);
                if(isset($info[3]) && $info[3] != '') $student->setClass(preg_replace('#^[0-9]+_(.+)$#', '$1', $info[3]));
                if(isset($info[4]) && $info[4] != '') $student->setEmail($info[4]);
                $this->em->persist($student);
                $this->em->flush();

                $this->error++;
            }

            $ids[] = $info[0];
        }

        #Softdelete any missing student from the CSV
        $students = $this->em->getRepository('FerusStudentBundle:Student')->findByNotDeleted();
        $date = new \DateTime();
        foreach($students as $student){
            if(!in_array($student->getId(), $ids)){
                if($student->hasFairpay()){
                    if($student->getAccount()->getBalance() != "0.00"){
                        $class = $student->getClass();
                        $newClass = preg_replace('#^E[1-5](.*)$#', "E6$1", $class);
                        if($newClass === $class) $student->setClass('E6');
                        else $student->setClass($newClass);
                    }
                    else{
                        $student->getAccount()->setDeletedAt($date);
                        $student->setDeletedAt($date);
                    }
                }
                else{
                    $student->setDeletedAt($date);
                }

                $student->setIsContributor(false);
                $this->em->persist($student);
                $this->em->flush();

                $this->error++;
            }
        }

        return $this->success . ' étudiants créés et '.$this->error.' mis à jour.';
    }
} 