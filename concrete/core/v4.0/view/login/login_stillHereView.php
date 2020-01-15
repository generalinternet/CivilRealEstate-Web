<?php

class LoginStillHereView extends AbstractLoginStillHereView {
    
    public function buildForm() {
        $this->openViewWrap();
        $this->form->addHTML('<h1>Are you still there?</h1>');
        $this->form->addHTML('<p>You will be logged out in 5 minutes.</p>');
        $this->form->addHTML('<span class="button button_theme_primary">I’m still here</span>');
        $this->closeViewWrap();
    }

}
