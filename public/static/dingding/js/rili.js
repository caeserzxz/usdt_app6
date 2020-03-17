var $$ = function(id) {
  return "string" == typeof id ? document.getElementById(id) : id;
};
var Class = {
  create: function() {
    return function() {
      this.initialize.apply(this, arguments);
    };
  }
};
Object.extend = function(destination, source) {
  for (var property in source) {
    destination[property] = source[property];
  }
  return destination;
};
var Calendar = Class.create();
Calendar.prototype = {
  initialize: function(container, options) {
    this.Container = $$(container); //容器(table结构)
    this.Days = []; //日期对象列表
    this.SetOptions(options);
    this.onToday = this.options.onToday;
    this.onSignIn = this.options.onSignIn;
    this.onFinish = this.options.onFinish;
    this.qdDay = this.options.qdDay;
    this.jifenNum = this.options.jifenNum;
    this.nowtime = this.options.nowtime;
    this.Year = new Date(this.nowtime * 1000).getFullYear();
    this.Month = new Date(this.nowtime * 1000).getMonth() + 1;
    this.isSignIn = false;
    this.Draw();
  },
  //设置默认属性
  SetOptions: function(options) {
    this.options = {
      //默认值
      qdDay: null,
      nowtime: null, //今天
      jifenNum: null, // 积分
      onToday: function() {}, //已签到
      onSignIn: function() {}, //今天是否签到
      onFinish: function() {} //日历画完后触发
    };
    Object.extend(this.options, options || {});
  },
  //上一个月
  PreMonth: function() {
    //先取得上一个月的日期对象
    var d = new Date(this.Year, this.Month - 2, 1);
    //再设置属性
    this.Year = d.getFullYear();
    this.Month = d.getMonth() + 1;
    //重新画日历
    this.Draw();
  },
  //下一个月
  NextMonth: function() {
    var d = new Date(this.Year, this.Month, 1);
    this.Year = d.getFullYear();
    this.Month = d.getMonth() + 1;
    this.Draw();
  },
  //画日历
  Draw: function() {
    //签到日期
    var day = this.qdDay;
    //日期列表
    var arr = [];
    var jifenNum = this.jifenNum;
    //用当月第一天在一周中的日期值作为当月离第一天的天数
    var tianNums = new Date(this.Year, this.Month - 1, 0).getDate();
    var chazhi = new Date(this.Year, this.Month - 1, 1).getDay();
    var monthDay = new Date(this.Year, this.Month, 0).getDate();
    for (let i = 1; i <= chazhi; i++) {
      let monthDate = new Date(this.Year, this.Month - 2, 0);
      let PreMonths = monthDate.getDate() - i;
      // 本月头部上个月的日期
      arr.unshift(PreMonths);
    }
    //用当月最后一天在一个月中的日期值作为当月的天数
    for (let i1 = 1; i1 <= monthDay; i1++) {
      console.log(i1);
      arr.push(i1);
      if (this.IsSame(new Date(this.Year, this.Month - 1, d), this.nowtime)) {
        cell.className = "active3";
        cell.innerHTML =
          "<span data-chunk=" +
          (this.Month - 1) +
          ">" +
          d +
          "</span><em>可领<br/>" +
          jifenNum[d - 1] +
          "积分</em>";
      }
    }
    var frag = document.createDocumentFragment();
    this.Days = [];
    while (arr.length > 0) {
      var end_day = 1;
      //每个星期插入一个tr
      var row = document.createElement("tr");
      //每个星期有7天
      for (let i2 = 1; i2 <= 7; i2++) {
        var cell = document.createElement("td");
        cell.innerHTML = "&nbsp;";
        if (arr.length > 0) {
          var d = arr.shift();
          if (chazhi > 0) {
            cell.innerHTML =
              "<span style='color:#CCCCCC;' data-chunk=" +
              (this.Month - 2) +
              ">" +
              d +
              "</span>";
            chazhi--;
          } else if (
            new Date(this.Year, this.Month - 1, d).getTime() <
            new Date(this.nowtime * 1000).getTime()
          ) {
            cell.className = "active1";
            cell.innerHTML =
              "<span data-chunk=" +
              (this.Month - 1) +
              ">" +
              d +
              "</span><em>未领<br/>" +
              jifenNum[d - 1] +
              "积分</em>";
          } else if (
            new Date(this.Year, this.Month - 1, d).getTime() >
            new Date(this.nowtime * 1000).getTime()
          ) {
            cell.className = "active3";
            cell.innerHTML =
              "<span data-chunk=" +
              (this.Month - 1) +
              ">" +
              d +
              "</span><em>预领<br/>" +
              jifenNum[d - 1] +
              "积分</em>";
          } else {
            cell.innerHTML =
              "<span data-chunk=" + (this.Month - 1) + ">" + d + "</span>";
          }

          if (d > 0 && day.length) {
            for (let ii = 0; ii < day.length; ii++) {
              this.Days[d] = cell;
              //已签到
              if (
                this.IsSame(
                  new Date(
                    this.Year,
                    cell.querySelector("span").getAttribute("data-chunk"),
                    d
                  ),
                  day[ii]
                )
              ) {
                this.onToday(cell);
              }
              //判断今天是否签到
              if (this.checkSignIn(new Date(this.nowtime * 1000), day[ii])) {
                this.onSignIn();
              }
            }
          }
        } else {
          cell.innerHTML =
            "<span style='color:#CCCCCC;' data-chunk=" +
            this.Month +
            ">" +
            end_day +
            "</span>";
          end_day++;
        }
        row.appendChild(cell);
      }

      frag.appendChild(row);
    }
    //先清空内容再插入(ie的table不能用innerHTML)
    while (this.Container.hasChildNodes()) {
      this.Container.removeChild(this.Container.firstChild);
    }
    this.Container.appendChild(frag);
    this.onFinish();
    if (this.isSignIn) {
      this.isSignIn = false;
      return this.SignIn();
    }
  },
  //是否签到
  IsSame: function(d1, d2) {
    d2 = new Date(d2 * 1000);
    return (
      d1.getFullYear() == d2.getFullYear() &&
      d1.getMonth() == d2.getMonth() &&
      d1.getDate() == d2.getDate()
    );
  },
  //今天是否签到
  checkSignIn: function(d1, d2) {
    d2 = new Date(d2 * 1000);
    return (
      d1.getFullYear() == d2.getFullYear() &&
      d1.getMonth() == d2.getMonth() &&
      d1.getDate() == d2.getDate()
    );
  },
  //签到
  SignIn: function() {
    var now = new Date(this.nowtime * 1000);
    var Year = now.getFullYear();

    var day = now.getDate();
    var tb = document.getElementById("idCalendar");
    for (var i = 0; i < tb.rows.length; i++) {
      for (var j = 0; j < tb.rows[i].cells.length; j++) {
        if (
          day == tb.rows[i].cells[j].innerText &&
          Year == this.Year &&
          tb.rows[i].cells[j]
            .querySelector("span")
            .getAttribute("data-chunk") ==
            this.Month - 1
        ) {
          if (tb.rows[i].cells[j].className == "active2") {
            return 2;
          }
          var newTd = $(tb.rows[i].cells[j])
            .children("em")
            .text();
          getNum = parseInt(newTd.slice(2, -2));
          tb.rows[i].cells[j].className = "active2";
          tb.rows[i].cells[j].innerHTML =
            "<span>" + day + "</span><em>已领<br/>" + getNum + "积分</em >";
          this.qdDay.push(Date.parse(new Date()) / 1000);
          return 1;
        }
      }
    }
  }
};
