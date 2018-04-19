var content = [{
    t: "Резкие перепады аппетита.",
    w: 1
}, {
    t: "Пропал интерес ко всему.",
    w: 1
}, {
    t: "Отсутствие сексуального влечения.",
    w: 1
}, {
    t: "Сбой ритма суточной активности (до полудня – сонливость, после полудня – повышенная активность, к ночи – бессонница).",
    w: 1
}, {
    t: "Резкие перепады настроения.",
    w: 1
}, {
    t: "Снижение умственных способностей.",
    w: 1
}, {
    t: "Ослабление памяти.",
    w: 1
}, {
    t: "Не стремится к самоутверждению и самовыражению.",
    w: 1
}, {
    t: "Ослабление силы воли.",
    w: 1
}, {
    t: "Повышенная скрытность.",
    w: 1
}, {
    t: "Усиление нигилизма (отрицательной оценки окружающего мира).",
    w: 1
}, {
    t: "Сложности в общении с близкими.",
    w: 1
}, {
    t: "Не исполняет обещания, обязанности и договоренности.",
    w: 1
}, {
    t: "Угрюмость и раздражительность в поведении.",
    w: 1
}, {
    t: "Следы на теле от неизвестного вам укола.",
    w: 3
}, {
    t: "Расширенные зрачки, не реагирующие на свет.",
    w: 2
}, {
    t: "Суженные до точки зрачки.",
    w: 2
}, {
    t: "Зуд по всему телу (периодические почесывания).",
    w: 2
}, {
    t: "Повышенное потоотделение независимо от температуры.",
    w: 2
}, {
    t: "Сильная жажда.",
    w: 1
}, {
    t: "Быстро говорит, не слушая собеседника. В разговоре отсутствует логика.",
    w: 2
}, {
    t: "Сильно покрасневшие («налитые кровью») глаза.",
    w: 1
}, {
    t: "Резкая потеря веса (без диет).",
    w: 2
}, {
    t: "Бессмысленный взгляд в пространство.",
    w: 3
}, {
    t: "Безудержный смех без причины.",
    w: 2
}, {
    t: "Резкий необычный запах.",
    w: 1
}, {
    t: "Безосновательные эмоции (безудержный смех, плач, гнев).",
    w: 1
}, {
    t: "Гриппозное состояние (насморк и озноб).",
    w: 1
}, {
    t: "Лживость без мотивов.",
    w: 1
}, {
    t: "Воровство у близких людей.",
    w: 1
}];

var cmt = {
    content: [{
        t: "no content",
        w: 1
    }],
    rules: {
        name: 'Детектор',
        treshhold: 9
    },
    setYes: function(ctx) {
        ctx.answer(ctx.weight);
        cmt.result.addAnswer(ctx.weight);
        ctx.next(ctx);
    },
    setNo: function(ctx) {
        ctx.next(ctx);
    },
    next: function(ctx) {
        ctx.active(false);
        if (cmt.questions[ctx.index + 1]) {
            cmt.questions[ctx.index + 1].active(true);
        } else {
            cmt.result.done();
        }
    },
    Question: function(obj, index) {
        return {
            index: index,
            text: obj.t,
            weight: obj.w,
            answer: ko.observable(0),
            active: ko.observable(false),
            setYes: cmt.setYes,
            setNo: cmt.setNo,
            next: cmt.next
        }
    },
    questions: [],
    result: {
        active: ko.observable(false),
        alarm: ko.observable(false),
        success: ko.observable(false),
        count: ko.observable(0),
        addAnswer: function(count) {
            cmt.result.count(cmt.result.count() + count);
        },
        done: function() {
            cmt.result.active(true);
            if (cmt.result.count() >= cmt.rules.treshhold) {
                cmt.result.alarm(true);
            } else {
                cmt.result.success(true);
            }
        },
        reStart: function() {
            console.log('restart');
            cmt.result.active(false);
            cmt.result.success(false);
            cmt.result.alarm(false);
            cmt.result.count(0);
            for (var i = 0, l = cmt.questions; i < l; i++) {
                cmt.questions[i].answer(0);
            }
            cmt.questions[0].active(true);
        }
    },
    init: function(obj) {
        cmt.questions = [];
        for (var i = 0, l = obj.length; i < l; i++) {
            cmt.questions.push(new cmt.Question(obj[i], i));
        }
        cmt.questions[0].active(true);
    }
};

cmt.init(content);
ko.applyBindings(cmt);