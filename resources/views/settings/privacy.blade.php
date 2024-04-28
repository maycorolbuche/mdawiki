@extends('settings.layouts.default')
@section('title', 'Política de Privacidade')

@section('card')
    <x-card title="Política de Privacidade">
        @include('layouts.partials.messages')

        <div class="row">
            <div class="col-12">
                <div class="mb-3">
                    <p>
                        Esta página informa sobre nossas políticas relativas à coleta, uso e divulgação de dados pessoais
                        quando você usa nosso Serviço e os direitos associados a esses dados. Respeitamos sua privacidade e
                        estamos comprometidos em proteger as informações pessoais que você nos fornece.
                    </p>

                    <x-title level=2 class="mt-2">Informações que Coletamos</x-title>
                    <ul>
                        <li>
                            <b>Dados Pessoais:</b> Podemos solicitar que você nos forneça certas informações pessoais
                            identificáveis que podem ser usadas para contatá-lo ou identificá-lo. Essas informações podem
                            incluir, mas não estão limitadas a:
                            <ul>
                                <li>Nome</li>
                                <li>Endereço de e-mail</li>
                                <li>Número de telefone</li>
                                <li>Endereço</li>
                                <li>Cookies e dados de uso</li>
                            </ul>
                        </li>
                        <li>
                            <b>Dados de Uso:</b> Podemos também coletar informações sobre como o Serviço é acessado e
                            utilizado ("Dados de Uso"). Esses Dados de Uso podem incluir informações como o endereço de
                            protocolo de Internet do seu computador (por exemplo, endereço IP), tipo de navegador, versão do
                            navegador, as páginas do nosso Serviço que você visita, a hora e a data da sua visita, o tempo
                            gasto nessas páginas, identificadores exclusivos e outros dados de diagnóstico.
                        </li>
                    </ul>

                    <x-title level=2 class="mt-2">Uso de Dados</x-title>

                    <p>Nós usamos os dados coletados para diversos fins, incluindo:</p>
                    <ul>
                        <li>Fornecer e manter nosso Serviço</li>
                        <li>Notificá-lo sobre mudanças em nosso Serviço</li>
                        <li>Coletar análises ou informações valiosas para que possamos melhorar nosso Serviço</li>
                        <li>Monitorar o uso do nosso Serviço</li>
                        <li>Detectar, prevenir e resolver problemas técnicos</li>
                    </ul>

                    <x-title level=2 class="mt-2">Seus Direitos de Privacidade na LGPD</x-title>
                    <p>A Lei Geral de Proteção de Dados (LGPD) concede certos direitos aos indivíduos em relação aos
                        seus dados pessoais. Isso inclui o direito de acessar, corrigir, excluir ou limitar o
                        processamento de seus dados pessoais. Para exercer esses direitos ou para quaisquer dúvidas
                        sobre como tratamos seus dados pessoais, entre em contato conosco através do e-mail <a
                            href="mailto:{{ env('MAIL_REPLY_TO') }}">{{ env('MAIL_REPLY_TO') }}</a>.</p>

                    <x-title level=2 class="mt-2">Contate-Nos</x-title>
                    <p>Se você tiver alguma dúvida sobre esta Política de Privacidade, entre em contato conosco
                        através do e-mail <a href="mailto:{{ env('MAIL_REPLY_TO') }}">{{ env('MAIL_REPLY_TO') }}</a>.
                    </p>
                </div>
            </div>
        </div>

    </x-card>
@endsection
