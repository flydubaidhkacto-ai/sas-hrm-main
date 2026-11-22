@if ($user->join_date && \Carbon\Carbon::parse($user->join_date)->format('m-d-Y') == date('m-d-Y'))
        <div class="col-md-12 col-sm-12 col-lg-12 col-xl-6">
            <div class="card dash-widget">
                <div class="card-body"> <span class="dash-widget-icon"><i class="fa fa-handshake-o"></i></span>
                    <div class="dash-widget-info">
                        <span>Dear <b>{{ $user->name }}</b>, We want to congratulate you on your <button
                                id="demo" style="display: none">Birthday</button> Joining Day. Our
                            blessings and best wishes will accompany you for the year ahead. We are happy to work by
                            your side and hope you know you have our full trust and support.</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
