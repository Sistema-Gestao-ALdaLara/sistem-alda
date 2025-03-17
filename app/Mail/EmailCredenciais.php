<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailCredenciais extends Mailable
{
    use Queueable, SerializesModels;
    protected $usuario;
    protected $senha;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $usuario, $senha)
    {
        $this->usuario = $usuario;
        $this->senha = $senha;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->view('school.credenciais')->with([
            'email' => $this->usuario->email,
            'senha' => $this->senha,
        ]);

        $this->withSwiftMessage(function ($message) {
            $message->getHeaders()->addTextHeader(
                'Custom-Header', 'Header Value'
            );
        });

        return $this;
    }
}
