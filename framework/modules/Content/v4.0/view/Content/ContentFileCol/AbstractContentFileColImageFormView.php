<?php
/**
 * Description of GI_Model
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractContentFileColImageFormView extends AbstractContentFileColFormView{
    
    /**
     * Override buildFormGuts
     */
    public function buildFormGuts() {
        $this->form->addField($this->content->getFieldName('type_ref'), 'hidden', array(
            'value' => $this->content->getTypeRef()
        ));
        
        $this->form->addField($this->content->getFieldName('title'), 'hidden', array(
            'value' => $this->content->getProperty('title')
        ));
        
        $this->form->addField($this->content->getFieldName('ref'), 'hidden', array(
            'value' => $this->content->getProperty('ref')
        ));
        
        $this->form->addField($this->content->getFieldName('content'), 'hidden', array(
            'value' => $this->content->getProperty('content_file_col.content')
        ));
        
        if($this->uploader){
            $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
            $this->form->addHTML($this->uploader->getHTMLView());
            $this->form->addHTML('</div>')
                ->addHTML('<div class="column">')
                ->addHTML('</div>')
            ->addHTML('</div>');
            
            $this->form->addHTML('<div class="columns halves">');
            $this->form->addField($this->content->getFieldName('image_size'), 'dropdown', array(
                'displayName' => 'Image Size',
                'options' => ContentFileColImage::$OPTIONS_SIZE,
                'hideNull' => true,
                'value' => $this->content->getProperty('content_file_col.image_size'),
                'formElementClass' => 'column',
            ));
            
            $this->form->addField($this->content->getFieldName('image_align'), 'dropdown', array(
                'displayName' => 'Image Alignment',
                'options' => ContentFileColImage::$OPTIONS_ALIGN,
                'hideNull' => true,
                'value' => $this->content->getProperty('content_file_col.image_align'),
                'formElementClass' => 'column',
            ));
            $this->form->addHTML('</div>');
        }
    }
}
