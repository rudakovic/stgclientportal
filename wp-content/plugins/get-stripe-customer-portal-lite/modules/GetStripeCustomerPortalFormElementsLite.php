<?php

    class GetStripeCustomerPortalFormElementsLite {
        var $type;
        var $settings;
        var $content;

        function __construct( $type, $parameters, $value ){
            $this->type = $type;
            $this->parameters = $parameters;

            // array empty patch
            $default_array = [
                'class' => '',
                'id' => '',
                'value' => '',
                'default' => '',
                'width' => '',
                'title' => '',
                'sub_title' => '',
                'sub_text' => '',
                'rows' => '',
                'name' => '',
                'href' => '',
                'style' => '',
                'upload_text' => '',
                'placeholder' => '',
            ];
            $this->parameters = array_merge( $default_array, $this->parameters );
            $this->value = $value;
            $this->generate_result_block();
        }

        function generate_result_block() {
            global $post;

            $out = '';
            switch( $this->type ){

                case "shortcode":
                    $out .= '
                        <div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
                            <div class="gscp-form-group">
                                <label class="gscp-control-label" for="'.$this->parameters['id'].'">'.$this->parameters['title'].'</label>
                                <input type="text" readonly class="form-control input-xlarge" value="['.$this->parameters['name'].' id=\''.$post->ID.'\']">
                              </div>
                        </div>
                    ';
                break;
                
                case "text":
                    $out .= '
                        <div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
                            <div class="gscp-form-group vertical">
                                <div class="gscp-field-left">
                                    <label class="gscp-control-label" for="'.$this->parameters['id'].'">'.$this->parameters['title'].'</label>
                                    <span class="gscp-field-description">'.$this->parameters['description'].'</span>
                                </div>
                                <div class="gscp-field-right">
                                    <input type="text" class="form-control '.$this->parameters['class'].'"  name="'.$this->parameters['name'].'" id="'.$this->parameters['id'].'" placeholder="'.$this->parameters['placeholder'].'" value="'.( $this->value && $this->value != '' ? esc_html( stripslashes( $this->value ) ) : $this->parameters['default'] ).'">
                                </div>
                              </div>
                        </div>
                    ';
                break;

                case "textarea":
                    $out .= '
                        <div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
                            <div class="gscp-form-group vertical">
                                <div class="gscp-field-left">
                                    <label class="gscp-control-label" for="'.$this->parameters['id'].'">'.$this->parameters['title'].'</label>
                                    <span class="gscp-field-description">'.$this->parameters['description'].'</span>
                                </div>
                                <div class="gscp-field-right">
                                    <textarea style="'.$this->parameters['style'].'" class="form-control '.$this->parameters['class'].'" name="'.$this->parameters['name'].'" id="'.$this->parameters['id'].'" rows="'.$this->parameters['rows'].'">'.( $this->value && $this->value != '' ? esc_html( stripslashes( $this->value ) ) : $this->parameters['default'] ).'</textarea>
                                </div>
                              </div>
                        </div>
                    ';
                break;

                case "button":
                    $out .= '
                        <div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
                            <div class="gscp-form-group">
                                <label class="gscp-control-label" for="">&nbsp;</label>
                                
                                  <a class="'.( $this->parameters['class'] ? $this->parameters['class'] : 'button' ).'" href="'.$this->parameters['href'].'">'.$this->parameters['title'].'</a>
                                  
                                
                            </div> 
                        </div>
                    ';
                break;

                case "select":
                    $out .= '
                        <div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
                            <div class="gscp-form-group vertical">
                                <div class="gscp-field-left">
                                    <label class="gscp-control-label" for="'.$this->parameters['id'].'">'.$this->parameters['title'].'</label>
                                    <span class="gscp-field-description">'.$this->parameters['description'].'</span>
                                </div>
                                <div class="gscp-field-right">
                                    <select style="'.$this->parameters['style'].'" class="form-control '.$this->parameters['class'].'" name="'.$this->parameters['name'].'" id="'.$this->parameters['id'].'">'; 
                                        if( count( $this->parameters['value'] ) > 0 )
                                            foreach( $this->parameters['value'] as $k => $v ){
                                                if( $this->value && $this->value != '' ){
                                                    $out .= '<option value="'.$k.'" '.( $this->value == $k ? ' selected ' : ' ' ).' >'.$v.'</option> ';
                                                }else{
                                                    $out .= '<option value="'.$k.'" '.( $this->parameters['default'] == $k ? ' selected ' : ' ' ).' >'.$v.'</option> ';
                                                }
                                            }
                            $out .= '
                                    </select>
                                </div>
                            </div>
                        </div>
                    ';
                break;

                case "checkbox":
                    $out .= '
                        <div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).' is_switch1">
                            <div class="gscp-form-group">
                                <div class="gscp-field-left">
                                    <input type="checkbox" class="'.$this->parameters['class'].' " type="checkbox" name="'.$this->parameters['name'].'" id="'.$this->parameters['id'].'" value="on" '.( $this->value == 'on' ? ' checked ' : '' ).'  >
                                </div>
                                <div class="gscp-field-right vertical">
                                    <label class="gscp-control-label" for="'.$this->parameters['id'].'">'.$this->parameters['title'].'</label>
                                    <span class="gscp-field-description">'.$this->parameters['description'].'</span>
                                </div>
                            </div>
                        </div>
                    ';
                break;

                case "radio":
                    $out .= '
                        <div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
                            <div class="gscp-form-group">
                                <label class="gscp-control-label" for="'.$this->parameters['id'].'">'.$this->parameters['title'].'</label>';
                                    foreach( $this->parameters['value'] as $k => $v ){
                                        $out .= '
                                        <label class="radio">
                                            <input  class="'.$this->parameters['class'].'" type="radio" name="'.$this->parameters['name'].'" id="'.$this->parameters['id'].'" value="'.$k.'" '.( $this->value == $k ? ' checked ' : '' ).' >&nbsp;  
                                            '.$v.'
                                          </label> ';
                                    }
                                $out .= '
                              </div>
                        </div>
                    ';
                break;

                case "multiselect":
                    $out .= '
                        <div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
                            <div class="gscp-form-group">
                                <label class="gscp-control-label" for="'.$this->parameters['id'].'">'.$this->parameters['title'].'</label>
                                  <select  multiple="multiple" style="'.$this->parameters['style'].'" class="form-control '.$this->parameters['class'].'" name="'.$this->parameters['name'].'[]" id="'.$this->parameters['id'].'">' ; 
                                  foreach( $this->parameters['value'] as $k => $v ){
                                      $out .= '<option value="'.$k.'" '.( @in_array( $k, $this->value )   ? ' selected ' : ' ' ).' >'.$v.'</option> ';
                                  }
                            $out .= '
                                  </select>
                              </div>
                        </div>
                    ';
                break;

                case "mediafile_single":
                    $attach_url = wp_get_attachment_url( $this->value );
                    $out .= '
                        <div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
                            <div class="gscp-form-group media_upload_block">
                            <label class="gscp-control-label" for="input01">'.$this->parameters['title'].'</label>
                             
                              <input type="hidden" class="form-control input-xlarge mediafile_single item_id" name="'.$this->parameters['name'].'" id="'.$this->parameters['name'].'" value="'.$this->value.'"> 
                              
                            
                              <input type="button" class="button upload_file" data-single="1" value="'.$this->parameters['upload_text'].'" />
                              <div class="image_preview">'.( $attach_url ?  $attach_url  : '' ).'</div>
                            </div> 
                        </div>
                    ';
                    break;
                    
                case "save":
                    $out .= '
                        <div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
                            <div class="form-actions">
                                <button type="submit" class="button button-primary">'.$this->parameters['title'].'</button>
                            </div> 
                        </div>
                    ';
                    break;

                case "link":
                    $out .= '
                        <div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
                            <div class="form-actions">
                                <a href="'.$this->parameters['href'].'" class="'.$this->parameters['class'].'">'.$this->parameters['title'].'</a>
                            </div> 
                        </div>
                    ';
                    break;
                
                case "text_out":
                    $out .= '
                        <div class="'.( $this->parameters['class'] ? $this->parameters['class'] : 'col-12' ).'">
                            '.$this->parameters['description'].'
                        </div>
                    ';
                break;
            }

            $this->content = $out;
        }

        public function get_code() {
            return $this->content;
        }
    }
 
?>