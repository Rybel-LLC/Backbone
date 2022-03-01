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

    /**
     * @param string $pageTitle
     * @param string|array|null $errors
     * @param bool|null $success
     */
    public function __construct(string $pageTitle, string|array|null $errors = null, bool|null $success = false)
    {
        $this->headers = array();
        $this->footers = array();
        $this->title = $pageTitle;
        $this->errors = $errors;
        $this->success = $success;
    }

    /**
     * @return void
     */
    public function render()
    {
        if ($this->page->requiresAuth && empty($_SESSION['id'])) {
            header("Location: /");
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

    /**
     * @param string $file
     * @return void
     */
    public function addHeader(string $file)
    {
        $this->headers[] = $file;
    }

    /**
     * @param string $file
     * @return void
     */
    public function addFooter(string $file)
    {
        $this->footers[] = $file;
    }

    /**
     * @param page $page
     * @return void
     */
    public function setPage(page $page)
    {
        $this->page = $page;
    }

    /**
     * @return void
     */
    private function renderErrors()
    {
        if ($this->errors == null) {
            return;
        }

        if (is_string($this->errors)) {
            $this->errors = array($this->errors);
        }

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

    /**
     * @return void
     */
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
