<?php

namespace App\Controller;

use App\Entity\SickDayWorkLog;
use App\Repository\BusinessTripWorkLogRepository;
use App\Repository\HomeOfficeWorkLogRepository;
use App\Repository\MaternityProtectionWorkLogRepository;
use App\Repository\OvertimeWorkLogRepository;
use App\Repository\ParentalLeaveWorkLogRepository;
use App\Repository\SickDayUnpaidWorkLogRepository;
use App\Repository\SickDayWorkLogRepository;
use App\Repository\SpecialLeaveWorkLogRepository;
use App\Repository\TimeOffWorkLogRepository;
use App\Repository\UserRepository;
use App\Repository\VacationWorkLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Welp\IcalBundle\Factory\Factory;
use Welp\IcalBundle\Response\CalendarResponse;

class CalendarController extends Controller
{
    /**
     * @var BusinessTripWorkLogRepository
     */
    private $businessTripWorkLogRepository;

    /**
     * @var HomeOfficeWorkLogRepository
     */
    private $homeOfficeWorkLogRepository;

    /**
     * @var MaternityProtectionWorkLogRepository
     */
    private $maternityProtectionWorkLogRepository;

    /**
     * @var OvertimeWorkLogRepository
     */
    private $overtimeWorkLogRepository;

    /**
     * @var ParentalLeaveWorkLogRepository
     */
    private $parentalLeaveWorkLogRepository;

    /**
     * @var SickDayUnpaidWorkLogRepository
     */
    private $sickDayUnpaidWorkLogRepository;

    /**
     * @var SickDayWorkLogRepository
     */
    private $sickDayWorkLogRepository;

    /**
     * @var SpecialLeaveWorkLogRepository
     */
    private $specialLeaveWorkLogRepository;

    /**
     * @var TimeOffWorkLogRepository
     */
    private $timeOffWorkLogRepository;

    /**
     * @var VacationWorkLogRepository
     */
    private $vacationWorkLogRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var Factory
     */
    private $iCalFactory;

    public function __construct(
        BusinessTripWorkLogRepository $businessTripWorkLogRepository,
        HomeOfficeWorkLogRepository $homeOfficeWorkLogRepository,
        MaternityProtectionWorkLogRepository $maternityProtectionWorkLogRepository,
        OvertimeWorkLogRepository $overtimeWorkLogRepository,
        ParentalLeaveWorkLogRepository $parentalLeaveWorkLogRepository,
        SickDayUnpaidWorkLogRepository $sickDayUnpaidWorkLogRepository,
        SickDayWorkLogRepository $sickDayWorkLogRepository,
        SpecialLeaveWorkLogRepository $specialLeaveWorkLogRepository,
        TimeOffWorkLogRepository $timeOffWorkLogRepository,
        VacationWorkLogRepository $vacationWorkLogRepository,
        UserRepository $userRepository,
        Factory $iCalFactory
    ) {
        $this->businessTripWorkLogRepository = $businessTripWorkLogRepository;
        $this->homeOfficeWorkLogRepository = $homeOfficeWorkLogRepository;
        $this->maternityProtectionWorkLogRepository = $maternityProtectionWorkLogRepository;
        $this->overtimeWorkLogRepository = $overtimeWorkLogRepository;
        $this->parentalLeaveWorkLogRepository = $parentalLeaveWorkLogRepository;
        $this->sickDayUnpaidWorkLogRepository = $sickDayUnpaidWorkLogRepository;
        $this->sickDayWorkLogRepository = $sickDayWorkLogRepository;
        $this->specialLeaveWorkLogRepository = $specialLeaveWorkLogRepository;
        $this->timeOffWorkLogRepository = $timeOffWorkLogRepository;
        $this->vacationWorkLogRepository = $vacationWorkLogRepository;
        $this->userRepository = $userRepository;
        $this->iCalFactory = $iCalFactory;
    }

    public function iCal(Request $request, string $icalUserKey): Response
    {
        $user = $this->userRepository->getByICalToken($icalUserKey);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $calendar = $this->iCalFactory->createCalendar();

        foreach ($this->businessTripWorkLogRepository->findAllRecentApprovedByUser($user) as $workLog) {
            $event = $this->iCalFactory->createCalendarEvent();
            $event->setUid(sprintf('businessTrip-%s', $workLog->getId()));
            $event->setStart((new \DateTime())->setTimestamp($workLog->getDate()->getTimeStamp()));
            $event->setAllDay(true);
            $event->setSummary('Dienstreise');
            $event->setDescription(sprintf(
                'Zweck: %s\nZiel: %s\nVerkehrsmittel: %s\nVoraussichtliche Abreise: %s\nVoraussichtliche Rückkehr: %s',
                $workLog->getPurpose(),
                $workLog->getDestination(),
                $workLog->getTransport(),
                $workLog->getExpectedDeparture(),
                $workLog->getExpectedArrival()
            ));
            $calendar->addEvent($event);
        }

        foreach ($this->homeOfficeWorkLogRepository->findAllRecentApprovedByUser($user) as $workLog) {
            $event = $this->iCalFactory->createCalendarEvent();
            $event->setUid(sprintf('homeOffice-%s', $workLog->getId()));
            $event->setStart((new \DateTime())->setTimestamp($workLog->getDate()->getTimeStamp()));
            $event->setAllDay(true);
            $event->setSummary('Mobile Arbeit');
            $event->setDescription(sprintf(
                'Kommentar: %s',
                $workLog->getComment()
            ));
            $calendar->addEvent($event);
        }

        foreach ($this->maternityProtectionWorkLogRepository->findAllRecentByUser($user) as $workLog) {
            $event = $this->iCalFactory->createCalendarEvent();
            $event->setUid(sprintf('maternityProtection-%s', $workLog->getId()));
            $event->setStart((new \DateTime())->setTimestamp($workLog->getDate()->getTimeStamp()));
            $event->setAllDay(true);
            $event->setSummary('Mutterschutz');
            $calendar->addEvent($event);
        }

        foreach ($this->parentalLeaveWorkLogRepository->findAllRecentByUser($user) as $workLog) {
            $event = $this->iCalFactory->createCalendarEvent();
            $event->setUid(sprintf('parentalLeave-%s', $workLog->getId()));
            $event->setStart((new \DateTime())->setTimestamp($workLog->getDate()->getTimeStamp()));
            $event->setAllDay(true);
            $event->setSummary('Elternzeit');
            $calendar->addEvent($event);
        }

        foreach ($this->sickDayUnpaidWorkLogRepository->findAllRecentByUser($user) as $workLog) {
            $event = $this->iCalFactory->createCalendarEvent();
            $event->setUid(sprintf('sickDayUnpaid-%s', $workLog->getId()));
            $event->setStart((new \DateTime())->setTimestamp($workLog->getDate()->getTimeStamp()));
            $event->setAllDay(true);
            $event->setSummary('Krank ohne Lohnfortzahlung');
            $calendar->addEvent($event);
        }

        foreach ($this->sickDayWorkLogRepository->findAllRecentByUser($user) as $workLog) {
            $event = $this->iCalFactory->createCalendarEvent();
            $event->setUid(sprintf('sickDay-%s', $workLog->getId()));
            $event->setStart((new \DateTime())->setTimestamp($workLog->getDate()->getTimeStamp()));
            $event->setAllDay(true);
            $event->setSummary('Kranktag');

            if ($workLog->getVariant() === SickDayWorkLog::VARIANT_WITH_NOTE) {
                $event->setDescription('Variante: Mit Krankenschein');
            } elseif ($workLog->getVariant() === SickDayWorkLog::VARIANT_WITHOUT_NOTE) {
                $event->setDescription('Variante: Ohne Krankenschein');
            } else {
                $event->setDescription(sprintf(
                    'Variante: Kind krank\nName des Kindes: %s\nGeburtstag des Kindes: %s',
                    $workLog->getChildName(),
                    $workLog->getChildDateOfBirth()->format('d.m.Y')
                ));
            }

            $calendar->addEvent($event);
        }

        foreach ($this->specialLeaveWorkLogRepository->findAllRecentApprovedByUser($user) as $workLog) {
            $event = $this->iCalFactory->createCalendarEvent();
            $event->setUid(sprintf('specialLeave-%s', $workLog->getId()));
            $event->setStart((new \DateTime())->setTimestamp($workLog->getDate()->getTimeStamp()));
            $event->setAllDay(true);
            $event->setSummary('Sonderurlaub');
            $calendar->addEvent($event);
        }

        foreach ($this->timeOffWorkLogRepository->findAllRecentApprovedByUser($user) as $workLog) {
            $event = $this->iCalFactory->createCalendarEvent();
            $event->setUid(sprintf('timeOff-%s', $workLog->getId()));
            $event->setStart((new \DateTime())->setTimestamp($workLog->getDate()->getTimeStamp()));
            $event->setAllDay(true);
            $event->setSummary('Freizeitausgleich');
            $event->setDescription(sprintf(
                'Kommentar: %s',
                $workLog->getComment()
            ));
            $calendar->addEvent($event);
        }

        foreach ($this->vacationWorkLogRepository->findAllRecentApprovedByUser($user) as $workLog) {
            $event = $this->iCalFactory->createCalendarEvent();
            $event->setUid(sprintf('vacation-%s', $workLog->getId()));
            $event->setStart((new \DateTime())->setTimestamp($workLog->getDate()->getTimeStamp()));
            $event->setAllDay(true);
            $event->setSummary('Urlaub');
            $calendar->addEvent($event);
        }

        return new CalendarResponse($calendar, 200, []);
    }
}
