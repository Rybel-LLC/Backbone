<?php /** @noinspection PhpIncludeInspection */

namespace Rybel\backbone;

class site
{
    private $headers;
    private $footers;
    private $page;
    private $title;
    private $errors;
    private $success;

    public function __construct($pageTitle, $errors = null, $success = false)
    {
        $this->headers = array();
        $this->footers = array();
        $this->title = $pageTitle;
        $this->errors = $errors;
        $this->success = $success;
    }

    public function render()
    {
        if ($this->page->requiresAuth && empty($_SESSION['id'])) {
            header("Location: index.php");
            die();
        }

        foreach ($this->headers as $header) {
            include $header;
        }
        echo '<title>' . $this->title . '</title>';

        $this->renderErrors();
        $this->renderSuccess();

        $this->page->render();

        foreach ($this->footers as $footer) {
            include $footer;
        }
    }

    public function addHeader($file)
    {
        $this->headers[] = $file;
    }

    public function addFooter($file)
    {
        $this->footers[] = $file;
    }

    public function setPage(page $page)
    {
        $this->page = $page;
    }

    private function renderErrors()
    {
        foreach ($this->errors as $error) {
            echo "<script>

            // Check if there is bootstrap
            if (!$.fn.modal) {
                document.write('<div class=\"alert alert-danger mt-3 mx-5\" role=\"alert\">" . $error . "</div>');
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
            if (!$.fn.modal) {
                document.write('<div class=\"alert alert-success mt-3 mx-5\" role=\"alert\">Success!</div>');
            } else {
                alert('Success!')
            }
            
            </script>";
        }
    }
}
