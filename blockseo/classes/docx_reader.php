<?php

class Docx_reader {

    private $fileData = false;
    private $errors = array();
    private $styles = array();

    public function __construct() {
        
    }

    private function load($file) {
        if (file_exists($file)) {
            $zip = new ZipArchive();
            $openedZip = $zip->open($file);
            if ($openedZip === true) {
                //attempt to load styles:
                if (($styleIndex = $zip->locateName('word/styles.xml')) !== false) {
                    $stylesXml = $zip->getFromIndex($styleIndex);
                    $xml = simplexml_load_string($stylesXml);
                    $namespaces = $xml->getNamespaces(true);

                    $children = $xml->children($namespaces['w']);

                    foreach ($children->style as $s) {
                        $attr = $s->attributes('w', true);
                        if (isset($attr['styleId'])) {
                            $tags = array();
                            $attrs = array();
                            foreach (get_object_vars($s->rPr) as $tag => $style) {
                                $att = $style->attributes('w', true);
                                switch ($tag) {
                                    case "b":
                                        $tags[] = 'strong';
                                        break;
                                    case "i":
                                        $tags[] = 'em';
                                        break;
                                    case "color":
                                        //echo (String) $att['val'];
                                        $attrs[] = 'color:#' . $att['val'];
                                        break;
                                    case "sz":
                                        $attrs[] = 'font-size:' . $att['val'] . 'px';
                                        break;
                                }
                            }
                            $styles[(String)$attr['styleId']] = array('tags' => $tags, 'attrs' => $attrs);
                        }
                    }
                    $this->styles = $styles;
                }

                //attempt to load link
                if (($linkIndex = $zip->locateName('word/_rels/document.xml.rels')) !== false) {
                    $externalLinks = array();
                    $linkXml = $zip->getFromIndex($linkIndex);
                    $xml = simplexml_load_string($linkXml);
                    $namespaces = $xml->getNamespaces(true);

                    $children = $xml->children($namespaces['w']);

                    foreach ($children as $rel) {
                        if(!empty($rel->attributes()->TargetMode) && $rel->attributes()->TargetMode=="External"){
                            $id = strval($rel->attributes()->Id);;
                            $externalLinks[$id] = array('ExternalLink'=>$rel->attributes()->Target);
                        }
                    }
                    $this->links = $externalLinks;
                }

                if (($index = $zip->locateName('word/document.xml')) !== false) {
                    // If found, read it to the string
                    $data = $zip->getFromIndex($index);
                    // Close archive file
                    $zip->close();
                    return $data;
                }
                $zip->close();
            } else {
                switch($openedZip) {
                    case ZipArchive::ER_EXISTS:
                        $this->errors[] = 'File exists.';
                        break;
                    case ZipArchive::ER_INCONS:
                        $this->errors[] = 'Inconsistent zip file.';
                        break;
                    case ZipArchive::ER_MEMORY:
                        $this->errors[] = 'Malloc failure.';
                        break;
                    case ZipArchive::ER_NOENT:
                        $this->errors[] = 'No such file.';
                        break;
                    case ZipArchive::ER_NOZIP:
                        $this->errors[] = 'File is not a zip archive.';
                        break;
                    case ZipArchive::ER_OPEN:
                        $this->errors[] = 'Could not open file.';
                        break;
                    case ZipArchive::ER_READ:
                        $this->errors[] = 'Read error.';
                        break;
                    case ZipArchive::ER_SEEK:
                        $this->errors[] = 'Seek error.';
                        break;
                }
            }
        } else {
            $this->errors[] = 'File does not exist.';
        }
    }

    public function setFile($path) {
        $this->fileData = $this->load($path);
    }

    public function to_plain_text() {
        if ($this->fileData) {
            return strip_tags($this->fileData);
        } else {
            return false;
        }
    }

    public function to_html() {
        if ($this->fileData) {
            $xml = simplexml_load_string($this->fileData);
            $namespaces = $xml->getNamespaces(true);

            $children = $xml->children($namespaces['w']);

            $html = '<style>span.block { display: block; }</style>';

            // var_dump($this->fileData);
            $links= "";

            foreach ($children->body->p as $p) {
                $style = '';
                $startTags = array();
                $startAttrs = array();
                $openTags = '';
                $closeTags = '';

                if($p->pPr->pStyle) {                    
                    $objectAttrs = $p->pPr->pStyle->attributes('w',true);
                    $objectStyle = (String) $objectAttrs['val'];

                    $mytag = $objectStyle;
                    $pos = strpos($objectStyle,"Heading");
                    $postitle = strpos($objectStyle,"Titre");
                    if ($pos !== false || $postitle !== false) {
                        $rest = substr($objectStyle, -1);
                        $openTags.='<h' . $rest . '>';
                        $closeTags.='</h' . $rest . '>';
                    }
					
                    if(isset($this->styles[$objectStyle])) {
                        $startTags = $this->styles[$objectStyle]['tags'];
                        $startAttrs = $this->styles[$objectStyle]['attrs'];
                    }
                }
                
                if ($p->pPr->spacing) {
                    $att = $p->pPr->spacing->attributes('w', true);
                    if (isset($att['before'])) {
                        $style.='padding-top:' . ($att['before'] / 10) . 'px;';
                    }
                    if (isset($att['after'])) {
                        $style.='padding-bottom:' . ($att['after'] / 10) . 'px;';
                    }
                }

                $html.='<span class="block" style="' . $style . '">';
                $li = false;
                
                if ($p->pPr->numPr) {
                    $li = true;
                    $html.='<li>';
                }

                if($p->hyperlink) {
                    $objectAttrs = $p->hyperlink->attributes('r',true);
                    $objectStyle = (String) $objectAttrs['id'];

                    if(!empty($objectStyle)){
                        $externalLinks = $this->links;
                        $links = '<a class="1" target="_blank" href="'.$externalLinks[$objectStyle]['ExternalLink'].'">'.$p->hyperlink->r->t.'</a>';
                        $html .= '<a class="1" target="_blank" href="'.$externalLinks[$objectStyle]['ExternalLink'].'">'.$p->hyperlink->r->t.'</a>';
                    }
                }
                
                foreach ($p->r as $part) {
                   // echo $part->t;
                    $tags = $startTags;
                    $attrs = $startAttrs;                                        

                    foreach (get_object_vars($part->pPr) as $k => $v) {
                        if ($k = 'numPr') {
                            $tags[] = 'li';
                        }
                    }

                    foreach (get_object_vars($part->rPr) as $tag => $style) {
                        $att = $style->attributes('w', true);
                        
                        switch ($tag) {
                            case "b":
                                $tags[] = 'b';
                                break;
                            case "i":
                                $tags[] = 'i';
                                break;
                            case "color":
                                //echo (String) $att['val'];
                                $attrs[] = 'color:#' . $att['val'];
                                break;
                            case "sz":
                                $attrs[] = 'font-size:' . $att['val'] . 'px';
                                break;
                            case "u":
                                $tags[] = 'u';
                                break;
                        }
                    }
                    
                   // var_dump($tags);
                    foreach ($tags as $tag) {
						$pos = strpos($objectStyle,"Heading");

						if ($pos !== false) {
							$rest = substr($objectStyle, -1);
							$openTags='<h' . $rest . '>';
							$closeTags='</h' . $rest . '>';
						}
                        else{
							$openTags='<' . $tag . '>';
							$closeTags='</' . $tag . '>';
						}
                    }
                    
                    $html .= "<span>".$openTags . $part->t . $closeTags."</span>";
                    $links= "";
					$objectStyle="";
                }

                if ($li) {
                    $html.='</li>';
                }
                $html.="</span>";

            }

            //Trying to weed out non-utf8 stuff from the file:
            $regex = <<<'END'
/
  (
    (?: [\x00-\x7F]                 # single-byte sequences   0xxxxxxx
    |   [\xC0-\xDF][\x80-\xBF]      # double-byte sequences   110xxxxx 10xxxxxx
    |   [\xE0-\xEF][\x80-\xBF]{2}   # triple-byte sequences   1110xxxx 10xxxxxx * 2
    |   [\xF0-\xF7][\x80-\xBF]{3}   # quadruple-byte sequence 11110xxx 10xxxxxx * 3 
    ){1,100}                        # ...one or more times
  )
| .                                 # anything else
/x
END;
            preg_replace($regex, '$1', $html);

            return $html;
            exit();
        }
    }

    public function get_errors() {
        return $this->errors;
    }

    private function getStyles() {
        
    }

}
