  @foreach($chatdata as $chatdataa)

      @if($chatdataa['sender_id']!='1')
        <div class="msg left-msg">
          <div
           class="msg-img"
           style="background-image: url({{$chatdataa['user_image']}})"
          ></div>

          <div class="msg-bubble">
            <div class="msg-info">
              <div class="msg-info-name">{{$chatdataa['user_name']}}</div>
              <div class="msg-info-time">{{$chatdataa['date']}}</div>
            </div>

            <div class="msg-text">
               {{$chatdataa['message']}}
            </div>
          </div>
        </div>
      @else

    <div class="msg right-msg">
      <div
       class="msg-img"
       style="background-image: url({{$chatdataa['admin_image']}})"
      ></div>

      <div class="msg-bubble">
        <div class="msg-info">
          <div class="msg-info-name">{{$chatdataa['admin_name']}}</div>
          <div class="msg-info-time">{{$chatdataa['date']}}</div>
        </div>

        <div class="msg-text">
         {{$chatdataa['message']}}
        </div>
      </div>
    </div>
    @endif
  @endforeach 