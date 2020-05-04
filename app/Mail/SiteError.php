<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Throwable;

class SiteError extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $errorLocation;
    public $symfonyCss;
    public $symfonyHtml;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Throwable $error, $url = "Unknown Request")
    {
        $SymfonyDisplayer = new HtmlErrorRenderer(true);
        $FlattenException = FlattenException::createFromThrowable($error);

        $this->symfonyCss = $SymfonyDisplayer->getStylesheet($FlattenException);
        $this->symfonyHtml = $SymfonyDisplayer->getBody($FlattenException);
        $this->errorLocation = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        //$to = env('SITE_NAME', 'Infinity Next').' Webmaster <' . env('MAIL_ADDR_ADMIN', false) . '>';
        $to = env('MAIL_ADDR_ADMIN', false);
        $subject = "[" . env('SITE_NAME', 'Infinity Next') . " Error] {$this->errorLocation}";

        return $this->view('emails.error')
            ->to($to)
            ->subject($subject)
            ->with([
                //'exception' => $this->error,
                //'error_class' => get_class($this->error),
                'error_css' => $this->symfonyCss,
                'error_html' => $this->symfonyHtml,
            ]);
    }
}
