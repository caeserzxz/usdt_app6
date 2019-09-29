

<script type="text/javascript">
    var ws;
    // 连接服务端
    function connect() {
        // 创建websocket
        ws = new WebSocket("ws://<?=$_SERVER['SERVER_NAME']?>:2346");
        ws.onopen = onopen;
        // 当有消息时根据消息类型显示不同信息
        ws.onmessage = onmessage;
        ws.onclose = function() {
            console.log("连接关闭，正在重新连接");
            connect();
        };
        ws.onerror = function() {
            console.log("出现错误");
        };
    }
    // 服务端发来消息时
    function onmessage(e) {
        console.log(e.data);
    };
    // 连接建立时发送信息
    function onopen()
    {
        console.log("连接成功");
        ws.send('{"type":"login"}');
    }
    connect();
</script>
