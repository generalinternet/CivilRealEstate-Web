<?php

class LoginLayoutView extends AbstractLoginLayoutView {
    
    protected function addDefaultCSS() {
        $this->addCSS('https://fonts.googleapis.com/css?family=Montserrat:400,700&display=swap');
        parent::addDefaultCSS();
    }
    
    protected function addLayoutCSS(){
        $this->addCSS('resources/css/login_theme.css');
    }

    protected function openContentWrapDiv($class='') {
        $this->addHTML('<div id="content_wrap" class="section section_type_login banner banner_size_full-width banner_page_home">');
        $this->addHTML('<div class="container">');
        $this->addHTML('<div class="col-xs-12 col-md-6 col-md-push-3">');
        $this->addHTML('<div class="section__login-wrap">');
        return $this;
    }

    protected function closeContentWrapDiv(){
        $this->addHTML('</div>');
        $this->addHTML('</div>');
        $this->addHTML('</div>');
        $this->addHTML('</div>');
        return $this;
    }

    protected function addLogo($fileName = 'logo-header.png', $path="resources/media/img/logos/"){
        $this->addHTML('<div class="login__logo">');
        parent::addLogo($fileName, $path);
        $this->addHTML('</div>');
        return $this;
    }
}
