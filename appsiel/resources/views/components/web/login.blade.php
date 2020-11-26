<style>
    .estilo {
        margin-left: 800px;
        margin-top: -120px;
    }

    @media screen and (max-width: 782px) {

        .estilo {
            margin-top: 0px !important;
            margin-left: 0px !important;
            margin-bottom: 10px;
        }
    }

    .login {
        padding-top: 50px;
        <?php if ($login->tipo_fondo == 'COLOR') {
            echo "background-color: " . $login->fondo . ";";
        } ?>
    }
</style>
@if($login != null)
<section id="login">
    <div class="container-wrapper login">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <!-- IMAGEN -->
                    <img src="{{asset('img/'.$login->imagen)}}">
                </div>
                <div class="col-md-6">
                    <div class="contact-form">
                        <h3>Contact Info</h3>

                        <address>
                            <strong>Twitter, Inc.</strong><br>
                            795 Folsom Ave, Suite 600<br>
                            San Francisco, CA 94107<br>
                            <abbr title="Phone">P:</abbr> (123) 456-7890
                        </address>

                        <form id="main-contact-form" name="contact-form" method="post" action="#">
                            <div class="form-group">
                                <input type="text" name="name" class="form-control" placeholder="Name" required="">
                            </div>
                            <div class="form-group">
                                <input type="email" name="email" class="form-control" placeholder="Email" required="">
                            </div>
                            <div class="form-group">
                                <input type="text" name="subject" class="form-control" placeholder="Subject" required="">
                            </div>
                            <div class="form-group">
                                <textarea name="message" class="form-control" rows="8" placeholder="Message" required=""></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
@endif