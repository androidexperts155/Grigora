<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
</head>
<body>


<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header" style="margin-top: 150px;"><center><strong>{{ __('Reset Password') }}</strong></center></div>

                <div class="card-body">
                    <form method="POST" action="{{ url('password/update') }}">
                        @csrf

                        <input type="hidden" name="id" value="{{ $id }}">

                        <div class="form-group row">
                            <center><label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label></center>

                            <div class="col-md-6">
                                <center><input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password"></center>

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <center><strong>{{ $message }}</strong></center>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <center><label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label></center>

                            <div class="col-md-6">
                                <center><input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password"></center><br>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <center><button type="submit" class="btn btn-primary">
                                    {{ __('Reset Password') }}
                                </button></center>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>