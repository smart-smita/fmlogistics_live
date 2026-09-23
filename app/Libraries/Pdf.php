<?php 
namespace App\Libraries;
require APPPATH.'/ThirdParty/dompdf/autoload.inc.php';  // Adjust this path if necessary
use Dompdf\Dompdf;
use Dompdf\Options;

class Pdf{
    protected $dompdf;

    public function __construct()
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $this->dompdf = new Dompdf($options);
    }

    public function loadHtml($html)
    {
        $this->dompdf->loadHtml($html);
    }

    public function render()
    {
        $this->dompdf->render();
    }

    public function stream($filename = "document.pdf", $options = [])
    {
        $this->dompdf->stream($filename, $options);
    }

    // NEW CHANGE: Added missing output method
    public function output()
    {
        return $this->dompdf->output();
    }

    // NEW CHANGE: Added setPaper method for paper size and orientation
    public function setPaper($size = 'A4', $orientation = 'portrait')
    {
        $this->dompdf->setPaper($size, $orientation);
    }

    // Additional methods for setting paper size, orientation, etc., can be added here.
}
