<?php

namespace TheWebmen\CalendarField\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\Form;
use TheWebmen\CalendarField\Forms\CalendarField;

class GridFieldDetailForm_ItemRequestExtension extends Extension {

    /**
     * @param Form $form
     */
    public function updateItemEditForm(Form $form){

        if($form->getController()->getGridfield() instanceof CalendarField){
            $form->loadDataFrom($form->getController()->getRequest()->getVars());
        }

    }

}
