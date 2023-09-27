@component('mail::message')

@foreach($bounces as $bounce)
@component('mail::panel')
  {{ $bounce['sent_to'] }}: {{ trans('bouncemanager::messages.'.$bounce['reason']) }}
  
  {{ $bounce['subject'] }}
@endcomponent
@endforeach

@endcomponent