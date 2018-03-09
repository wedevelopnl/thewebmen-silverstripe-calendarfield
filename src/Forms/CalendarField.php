<?php

namespace TheWebmen\CalendarField\Forms;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\ORM\SS_List;
use SilverStripe\ORM\ValidationException;
use SilverStripe\View\Requirements;

class CalendarField extends GridField
{

    /** @var string  */
    private $startDate = '';
    /** @var string  */
    private $endDate = '';

    /** @var array  */
    private static $allowed_actions = [
        'loaddata',
        'saveevent'
    ];

    /**
     * CalendarField constructor.
     * @param string $name
     * @param null $title
     * @param SS_List|null $dataList
     * @param string $startDate
     * @param string $endDate
     */
    public function __construct($name, $title = null, SS_List $dataList = null, $startDate = 'Date', $endDate = 'Date')
    {
        $this->setStartDate($startDate);
        $this->setEndDate($endDate);

        $config = GridFieldConfig_RecordEditor::create();
        parent::__construct($name, $title, $dataList, $config);

        Requirements::css('https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.css');
        Requirements::css('thewebmen/silverstripe-calendarfield:resources/css/calendarfield.css');
        Requirements::javascript('https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.21.0/moment-with-locales.min.js');
        Requirements::javascript('https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.js');
        Requirements::javascript('thewebmen/silverstripe-calendarfield:resources/js/calendarfield.js');
    }

    /**
     * @param array $properties
     * @return \SilverStripe\ORM\FieldType\DBHTMLText
     */
    public function FieldHolder($properties = array())
    {
        $context = $this;

        if (count($properties)) {
            $context = $this->customise($properties);
        }

        return $context->renderWith($this->getFieldHolderTemplates());
    }

    /**
     * @param array $properties
     * @return \SilverStripe\ORM\FieldType\DBHTMLText
     */
    public function Field($properties = array())
    {
        $context = $this;

        $this->extend('onBeforeRender', $context, $properties);

        if (count($properties)) {
            $context = $context->customise($properties);
        }

        return $context->renderWith(__CLASS__);
    }

    /**
     * @return string
     */
    public function AddNewLink(){
        return Controller::join_links($this->Link('item'), 'new');
    }

    /**
     * @return HTTPResponse
     */
    public function loaddata()
    {
        $r = $this->getRequest();
        $start = $r->getVar('start');
        $end = $r->getVar('end');
        $list = $this->getList();

        $out = [];
        foreach($list as $listItem){
            $out[] = [
                'id' => $listItem->ID,
                'title' => $listItem->getTitle(),
                'start' => $listItem->Date,
                'startEditable' => true,
                'editlink' => Controller::join_links($this->Link('item'), $listItem->ID, 'edit')
            ];
        }

        $response = new HTTPResponse();
        $response->addHeader('Content-type', 'application/json');
        $response->setBody(Convert::raw2json($out));

        return $response;
    }

    /**
     * @return HTTPResponse
     */
    public function saveevent(){
        $r = $this->getRequest();

        $newstart = $r->postVar('newstart');
        $id = $r->postVar('id');

        $item = $this->getList()->find('ID', $id);
        if($item && $item->exists()){
            $item->{$this->getStartDate()} = $newstart;
            try {
                $item->write();
                $responseData = [
                    'success' => true,
                    'message' => 'Date saved'
                ];
            } catch (ValidationException $e) {
                $responseData = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }else{
            $responseData = [
                'success' => false,
                'message' => 'Item not found'
            ];
        }

        $response = new HTTPResponse();
        $response->addHeader('Content-type', 'application/json');
        $response->setBody(Convert::raw2json($responseData));

        return $response;
    }

    /**
     * @return string
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param string $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return string
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param string $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

}
