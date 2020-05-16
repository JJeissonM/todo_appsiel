@extends('web.templates.index')

@section('style')
    <link rel="stylesheet" href="{{asset('assets/tienda/css/skeleton.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/normalize.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/custom.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/main.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/login.css')}}">
@endsection

@section('content')
    @include('web.tienda.header')
    <main>
        <div class="main-container col1-layout">
            <div class="container">
                <div class="container-inner">
                    <div class="main">
                        <!-- Category Image-->
                        <!--   -->
                        <div class="col-main">
                            <div class="account-create">
                                <div class="page-title">
                                    <h1>Create an Account</h1>
                                </div>
                                <form action="" method="post" id="form-validate">
                                    <div class="fieldset">
                                        <input type="hidden" name="success_url" value="">
                                        <input type="hidden" name="error_url" value="">
                                        <h2 class="legend">Información Personal</h2>
                                        <ul class="form-list">
                                            <li class="fields">
                                                <div class="customer-name">
                                                    <div class="field name-firstname">
                                                        <label for="firstname" class="required"><em>*</em>First Name</label>
                                                        <div class="input-box">
                                                            <input type="text" id="firstname" name="firstname" value="" title="First Name" maxlength="255" class="input-text required-entry">
                                                        </div>
                                                    </div>
                                                    <div class="field name-lastname">
                                                        <label for="lastname" class="required"><em>*</em>Last Name</label>
                                                        <div class="input-box">
                                                            <input type="text" id="lastname" name="lastname" value="" title="Last Name" maxlength="255" class="input-text required-entry">
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <label for="email_address" class="required"><em>*</em>Email Address</label>
                                                <div class="input-box">
                                                    <input type="text" name="email" id="email_address" value="" title="Email Address" class="input-text validate-email required-entry">
                                                </div>
                                            </li>
                                            <li class="control">
                                                <div class="input-box">
                                                    <input type="checkbox" name="is_subscribed" title="Sign Up for Newsletter" value="1" id="is_subscribed" class="checkbox">
                                                </div>
                                                <label for="is_subscribed">Sign Up for Newsletter</label>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="fieldset">
                                        <h2 class="legend">Login Information</h2>
                                        <ul class="form-list">
                                            <li class="fields">
                                                <div class="field">
                                                    <label for="password" class="required"><em>*</em>Password</label>
                                                    <div class="input-box">
                                                        <input type="password" name="password" id="password" title="Password" class="input-text required-entry validate-password">
                                                    </div>
                                                </div>
                                                <div class="field">
                                                    <label for="confirmation" class="required"><em>*</em>Confirm Password</label>
                                                    <div class="input-box">
                                                        <input type="password" name="confirmation" title="Confirm Password" id="confirmation" class="input-text required-entry validate-cpassword">
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>

                                    </div>
                                    <div class="buttons-set">
                                        <p class="required">* Required Fields</p>
                                        <p class="back-link"><a href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/account/login/" class="back-link"><small>« </small>Back</a></p>
                                        <button type="submit" title="Submit" class="button"><span><span>Submit</span></span></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    @include('web.tienda.footer')
@endsection

@section('script')
    <script src="{{asset('assets/tienda/js/categories.js')}}"></script>
    <script type="text/javascript">
    </script>
@endsection
