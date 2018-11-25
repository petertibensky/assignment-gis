<div class="container">
    <div class="panel ">
        <div class="panel-body">
            <div class="panel-content">
                <form class="form-inline" method="POST" action="{{ route('searchRoute') }}">
                    {{ csrf_field() }}
                    <div class="form-row">
                        <div class="col-auto {{ $errors->has('from') ? ' has-error' : '' }}">
                            <label for="text" class="control-label">Z</label>

                            <div>
                                <input id="from" type="from" class="form-control mb-2 mr-sm-2" name="from"
                                       required
                                       autofocus value="{{ old('from') }}">

                                @if ($errors->has('from'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('from') }}</strong>
                                    </span>
                                @endif

                            </div>
                        </div>

                        <div class="col-auto {{ $errors->has('to') ? ' has-error' : '' }}">
                            <label for="text" class="control-label">Do</label>

                            <div>
                                <input id="to" type="to" class="form-control mb-2 mr-sm-2" name="to"
                                       required
                                       value="{{ old('to') }}">

                                @if ($errors->has('to'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('to') }}</strong>
                                    </span>
                                @endif

                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-4">
                            <button type="submit" class="btn btn-primary">
                                Hľadať
                            </button>

                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

