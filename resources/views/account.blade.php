@extends('layouts.app')

@section('content')
    <div class="container p-lg-1">
        <div class="row">
            <div class="col-lg-12 col-md-12 offset-md-2 offset-lg-0">
                @php
                    $number = rand(1,5);
                @endphp
                @if($number === 1)

                    <div class="card card-inverse card-square border-bottom-pink border-right-pink" style="border-top: none; border-left: none; background-color: transparent;">
                        <div class="card-block">
                            <h4 class="card-title pink">{{$name}}</h4>
                            <p class="card-text text-white">This is customer {{$name}}</p>

                            <div class="col-md-10 offset-1">
                                <div class="chart-container">
                                    <canvas id="myChart"></canvas>
                                </div>
                            </div>

                        </div>
                    </div>

                @elseif($number === 2)

                    <div class="card card-inverse card-square border-bottom-teal border-right-teal" style="border-top: none; border-left: none; background-color: transparent; ">
                        <div class="card-block">
                            <h4 class="card-title teal">{{$name}}</h4>
                            <p class="card-text text-white">This is customer {{$name}}</p>

                            <div class="col-md-10 offset-1">
                                <div class="chart-container">
                                    <canvas id="myChart"></canvas>
                                </div>
                            </div>

                        </div>
                    </div>

                @elseif($number === 3)

                    <div class="card card-inverse card-square border-bottom-purple border-right-purple" style="border-top: none; border-left: none; background-color: transparent;">
                        <div class="card-block">
                            <h4 class="card-title purple">{{$name}}</h4>
                            <p class="card-text text-white">This is customer {{$name}}</p>

                            <div class="col-md-10 offset-1">
                                <div class="chart-container">
                                    <canvas id="myChart"></canvas>
                                </div>
                            </div>

                        </div>
                    </div>

                @elseif($number === 4)

                    <div class="card card-inverse card-square border-bottom-yellow border-right-yellow" style="border-top: none; border-left: none; background-color: transparent;">
                        <div class="card-block">
                            <h4 class="card-title yellow">{{$name}}</h4>
                            <p class="card-text text-white">This is customer {{$name}}</p>

                            <div class="col-md-10 offset-1">
                                <div class="chart-container">
                                    <canvas id="myChart"></canvas>
                                </div>
                            </div>

                        </div>
                    </div>

                @else

                    <div class="card card-inverse card-square border-bottom-blue border-right-blue" style="border-top: none; border-left: none; background-color: transparent;">
                        <div class="card-block">
                            <h4 class="card-title blue">{{$name}}</h4>
                            <p class="card-text text-white">This is customer {{$name}}</p>

                            <div class="col-md-10 offset-1">
                                <div class="chart-container">
                                    <canvas id="myChart"></canvas>
                                </div>
                            </div>

                        </div>
                    </div>

                @endif
            </div>
        </div>

        <div class="col-lg-12 mt-5 mb-4" style="padding: 0;">
            {{--<hr class="mt-5" style="border-color: rgba(255, 255, 255, 0.2);">--}}
            <div class="row no-gutters">
                <div class="col-lg-6">
                    <div id="container_two"></div>
                </div>

                <div class="col-lg-6">
                    <div id="container_one"></div>
                </div>
            </div>
        </div>

        <div class="card card-square mb-lg-5" style="background-color: transparent; border: none; height: 400px;">
            <ul id="account-card-header" class="nav nav-tabs active-outline-card-header-pink" role="tablist">
                <li id="insights" class="nav-item">
                    <a id="insights-a" class="nav-link active-outline-tab-pink text-white" data-toggle="tab" href="#account-card-block-insights-tab" role="tab">Insights</a>
                </li>
                <li id="locations" class="nav-item">
                    <a id="locations-a" class="nav-link teal" data-toggle="tab" href="#account-card-block-locations-tab" role="tab">Locations</a>
                </li>
                <li id="contacts" class="nav-item">
                    <a id="contacts-a" class="nav-link blue" data-toggle="tab" href="#account-card-block-contacts-tab" role="tab">Contacts</a>
                </li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <div class="tab-pane card-block active active-outline-card-block-pink" id="account-card-block-insights-tab" role="tabpanel">
                    <h4 class="card-title text-white">Special title treatment</h4>
                    <p class="card-text text-white">With supporting text below as a natural lead-in to additional content.</p>
                    <a id="account-card-block-a" href="#" class="btn btn-nav-pink ">Go somewhere</a>
                </div>
                <div class="tab-pane card-block active-outline-card-block-teal" id="account-card-block-locations-tab" role="tabpanel">
                    @foreach($sites as $s)

                            <h4 class="card-title mt-2 text-white">{{$s->name}}</h4>
                            <div class="card-text text-white">
                                <ul class="list-group row" style="background-color: transparent;">
                                    <li for="name" class="col-lg-6 list-group-item" style="background-color: transparent; border: none;">
                                        <div class="col-lg-4">Phone Number</div>
                                        <div class="col-lg-6">{{$s->number}}</div>
                                    </li>

                                </ul>
                            </div>

                    @endforeach
                </div>

                <div class="tab-pane card-block active-outline-card-block-blue" id="account-card-block-contacts-tab" role="tabpanel">
                    <h4 class="card-title text-white">Special title treatment</h4>
                    <p class="card-text text-white">With supporting text below as a natural lead-in to additional content.</p>
                    <a id="account-card-block-a" href="#" class="btn btn-nav-pink ">Go somewhere</a>
                </div>
            </div>


        </div>

    </div>

@endsection