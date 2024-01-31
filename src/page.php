<?php 

namespace Rybel\backbone;

class page
{
    private $headers = [];
    private $footers = [];
    private $errors = [];
    private $success = false;
    private $authHelper;

    public function render($content)
    {
        foreach ($this->headers as $header) {
            include $header;
        }

        $this->renderErrors();
        $this->renderSuccess();

        echo $content;

        foreach ($this->footers as $footer) {
            include $footer;
        }
    }

    public function addHeader(string $file)
    {
        $this->headers[] = $file;
    }

    public function addFooter(string $file)
    {
        $this->footers[] = $file;
    }

    public function addError(string $error)
    {
        $this->errors[] = $error;
    }

    public function setSuccess(bool $success)
    {
        $this->success = $success;
    }
    
    private function renderErrors()
    {
        foreach ($this->errors as $error) {
            $error = addslashes($error);
            echo "<script>

            // Check if there is bootstrap
            if (typeof($.fn.popover) !== 'undefined') {
                document.write('<div class=\"alert alert-danger mt-3\" style=\"margin-left: 20px; margin-right: 20px\" role=\"alert\">" . $error . "</div>');
            } else {
                alert('" . $error . "')
            }
            
            </script>";
        }
    }

    private function renderSuccess()
    {
        if ($this->success) {
            echo "<script>

            // Check if there is bootstrap
            if (typeof($.fn.popover) !== 'undefined') {
                document.write('<div class=\"alert alert-success mt-3\" style=\"margin-left: 20px; margin-right: 20px\" role=\"alert\">Success!</div>');
            } else {
                alert('Success!')
            }
            
            </script>";
        }
    }
}
