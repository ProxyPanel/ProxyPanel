@extends('layout.static-mobile-master')

@section('header-script')
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/css/mdb.min.css" rel="stylesheet">
    <link href="{{ asset('assets/static/mobile/css/style.css') }}" rel="stylesheet">
@endsection

@section('content')
        <main>
            <div class="page-content page-content--subpage page-content--price">
                <div class="subpage-content">

                    <article class="entry">
                        <h1 class="entry__title">Hello, I am RitaVPN</h1>
                        <p class="entry__intro">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Architecto, nisi.</p>
                        <div class="entry__body">
                                <p><strong>What am I?</strong></p>
                                <p>VPN is a network technology that establishes a secure Internet connection. It is safe because you connect to a server operated by the VPN provider when you use it. In this way, only you can see the data sent between the server and your device for they are encrypted. Between computers or between a user and the Internet is a secret and secure tunnel for data traveling.</p>
                                    <p>A VPN connection is similar to a WAN connection but is more secure. The connection between one or more devices is created by a VPN manager (client / server) that uses VPN protocols such as OpenVPN, SoftEther, L2TP or other. That is to say, VPN establishes a tunnel between the user and the VPN server to ensure traffic security.</p>

                                    <p><strong>What can I do?</strong></p>

                                    <p>1.Anonymous internet access</p>

                                    <p>RitaVPN is dedicated to providing fast and private internet access anywhere in the world. You can Browse all or selected websites and apps anonymously.</p>

                                    <p>2. Hide IP address</p>

                                    <p>If you connect a mobile phone to a VPN, the phone shows as if it is on the same network as the VPN. The phone appears to have the IP address of the VPN server, masking your identity and location, so you can safely access the local network resources.</p>

                                    <p>3. Encrypt traffic</p>

                                    <p>All online traffic is sent via a secure connection to the VPN. No one can access your personal data. Your bank account information, emails and more won’t be hacked. This can be very beneficial for people using public WiFi.</p>

                                    <p>4. Bypass geo-blocks</p>

                                    <p>As we all know that there are some geo-blocks that hinder us from streaming content in other regions. People in one country may have no permission to watch TV shows of the major content provider.</p>

                                    <p><strong>Why you should use me?</strong></p>

                                    <p>Without the protection of VPN, your private data will be disclosed to others especially criminals and hackers. Recently, even NASA, undoubtedly the most technologically advanced organization on Earth, was hacked with a $25 Raspberry Pi. Why don’t you pay attention to your privacy?</p>

                                    <p>Furthermore, major TV and streaming sites all over the world are accessible to you. No more boring trip or missing out favorite content.</p>

                                    <p> Don’t hesitate, let’s start a no-border and secure access to the wonderful world.</p>
                                </div>
                        </article>
                </div>
            </div>
        </main>
@endsection

@section('footer-script')
    <!-- 3rd party JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
        crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"
        integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI"
        crossorigin="anonymous"></script>

    <script src="{{ asset('assets/static/mobile/js/app.js') }}"></script>

@endsection
