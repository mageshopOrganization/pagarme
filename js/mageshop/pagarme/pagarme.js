var PCIPagarMe = Class.create({
    initialize: function (config) {
        this.config = config;
    },
    addCardFieldsObserver: function (obj) {

        var cc_number = $$('input[name="payment[mageshop_pagarme_cc_number]"]').first();
        var cc_name = $$('input[name="payment[mageshop_pagarme_cc_name]"]').first();
        var exp_month = $$('select[name="payment[mageshop_pagarme_cc_exp_month]"]').first();
        var expiration_yr = $$('select[name="payment[mageshop_pagarme_cc_expiration_yr]"]').first();
        var cc_cvv = $$('input[name="payment[mageshop_pagarme_cc_cvv]"]').first();
        var cc_document = $$('input[name="payment[mageshop_pagarme_cc_document]"]').first();

        Element.observe(cc_number, 'change', function (e) { PCIPagarMeObj.placeorder = false; obj.tokenizeCard(); });
        Element.observe(cc_name, 'change', function (e) { PCIPagarMeObj.placeorder = false; obj.tokenizeCard(); });
        Element.observe(exp_month, 'change', function (e) { PCIPagarMeObj.placeorder = false; obj.tokenizeCard(); });
        Element.observe(expiration_yr, 'change', function (e) { PCIPagarMeObj.placeorder = false; obj.tokenizeCard(); });
        Element.observe(cc_cvv, 'change', function (e) { PCIPagarMeObj.placeorder = false; obj.tokenizeCard(); });
        if (typeof (cc_document) !== 'undefined') {
            Element.observe(cc_document, 'change', function (e) { PCIPagarMeObj.placeorder = false; obj.tokenizeCard(); });
        }

        PCIPagarMeObj.onClickForm();
    },
    tokenizeCard: function () {

        let _config = this.middlerCc();

        if( this.placeorder !== false ){
            return;
        }

        if (_config) {
            
            this.disablePlaceOrderButton();
            const options = {
                method: 'POST',
                headers: { accept: 'application/json', 'content-type': 'application/json' },
                body: JSON.stringify({
                    card: _config,
                    type: 'card'
                })
            };

            const url = `${this.config.url_app}${this.config.core}/${this.config.version}/tokens?appId=${this.config.public_key}`;

            fetch( url , options)
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(errData => {
                            throw new Error(JSON.stringify(errData));
                        });
                    }
                    return response.json();
                })
                .then(data => {
                
                    if (!data.id) {
                        return data.then(errData => {
                            throw new Error(JSON.stringify(errData));
                        });
                    }

                    $$('input[name="payment[mageshop_pagarme_cc_token]"]').first().value = data.id;
                    PCIPagarMeObj.enablePlaceOrderButton(); // Exemplo de como habilitar o botão de pedido após obter o token
                    // Reagenda o setTimeout após 57 segundos (um pouco antes do token expirar em 60 segundos)
                    this.tokenTimeout = setTimeout(function(){
                        PCIPagarMeObj.tokenizeCard()
                    }, 57000);

                })
                .catch(err => {
                    let errMessage = 'Erro ao obter token. Por favor, tente novamente.';
                    try {
                        const errData = JSON.parse(err.message);
                        if (errData.message && errData.errors) {
                            errMessage = `${errData.message}: ${Object.values(errData.errors).flat().join(', ')}`;
                        }
                    } catch (parseError) {
                        console.error('Erro ao processar a mensagem de erro:', parseError);
                    }
                    alert(errMessage);
                    PCIPagarMeObj.enablePlaceOrderButton(); // Reabilita o botão de pedido em caso de erro
                });
        }

        return;

    },
    onClickForm: function(){
        PCIPagarMeObj.placeorder = true;
        if(PCIPagarMeObj.config.placeorder_button && $$(PCIPagarMeObj.config.placeorder_button).first()){
            var placeorder_button = $$(PCIPagarMeObj.config.placeorder_button).first();
            Element.observe(placeorder_button, 'click', function (e) { PCIPagarMeObj.checkoutSubmitting(); });
        }
    },
    checkoutSubmitting: function (){
        window.clearTimeout(this.tokenTimeout); // Cancela o setTimeout atual se estiver agendado
    },
    middlerCc: function () {
        var cc_number = $$('input[name="payment[mageshop_pagarme_cc_number]"]').first().value.replace(/\s/g, '');
        var cc_name = $$('input[name="payment[mageshop_pagarme_cc_name]"]').first().value;
        var cc_exp_month = $$('select[name="payment[mageshop_pagarme_cc_exp_month]"]').first().value.replace(/^\s+|\s+$/g, '');
        var cc_expiration_yr = $$('select[name="payment[mageshop_pagarme_cc_expiration_yr]"]').first().value.replace(/^\s+|\s+$/g, '');
        var cc_cvv = $$('input[name="payment[mageshop_pagarme_cc_cvv]"]').first().value.replace(/^\s+|\s+$/g, '');
        var cc_document = null;

        if (cc_number === '' || cc_name === '' || cc_exp_month === '' || cc_expiration_yr === '' || cc_cvv === '') {
            return false;
        }

        if ($$('input[name="payment[mageshop_pagarme_cc_document]"]').first()) {
            cc_document = $$('input[name="payment[mageshop_pagarme_cc_document]"]').first().value.replace(/^\s+|\s+$/g, '');
            if (cc_document === '') {
                return false;
            }
        }

        return {
            holder_name: cc_name,
            exp_month: cc_exp_month,
            exp_year: cc_expiration_yr,
            cvv: cc_cvv,
            number: cc_number,
            holder_document: cc_document,
        }
    },
    disablePlaceOrderButton: function () {
        if (PCIPagarMeObj.config.placeorder_button) {
            if(typeof $$(PCIPagarMeObj.config.placeorder_button).first() != 'undefined'){

                $$(PCIPagarMeObj.config.placeorder_button).first().up().insert({
                    'after': new Element('div',{
                        'id': 'pagarme-loader'
                    })
                });

                $$('#pagarme-loader').first().setStyle({
                    'background': '#000000a1 url(\'' + PCIPagarMeObj.config.loader_url + '\') no-repeat center',
                    'height': $$(PCIPagarMeObj.config.placeorder_button).first().getStyle('height'),
                    'width': $$(PCIPagarMeObj.config.placeorder_button).first().getStyle('width'),
                    'left': document.querySelector(PCIPagarMeObj.config.placeorder_button).offsetLeft + 'px',
                    'z-index': 99,
                    'opacity': .5,
                    'position': 'absolute',
                    'top': document.querySelector(PCIPagarMeObj.config.placeorder_button).offsetTop + 'px'
                });

                return;
            }
        }
    },
    enablePlaceOrderButton: function(){
        if(PCIPagarMeObj.config.placeorder_button && typeof $$(PCIPagarMeObj.config.placeorder_button).first() != 'undefined'){
            if($$('#pagarme-loader').first()){
                $$('#pagarme-loader').first().remove();
            }
           
        }
    }
});