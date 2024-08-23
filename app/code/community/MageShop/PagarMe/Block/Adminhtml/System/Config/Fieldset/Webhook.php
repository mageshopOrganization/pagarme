<?php

class MageShop_PagarMe_Block_Adminhtml_System_Config_Fieldset_Webhook
extends Mage_Adminhtml_Block_System_Config_Form_Field
{


    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $element->getElementHtml();
        $html .= '<!-- Sua página HTML -->
            <div class="url-box" id="url-box">
                <span id="url-span">' . Mage::helper('mageshop_pagarme')->__(Mage::helper('mageshop_pagarme')->getUrlWebHook()) . '</span>
                <button id="botao-copiar" style="
                margin-top: 0px;
                padding: 3px 15px;
                background-color: #4CAF50;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                transition: background-color 0.3s;
                margin-left: 10px;
                margin-bottom: 5px;
                margin-top:5px;" value="' . Mage::helper('mageshop_pagarme')->__(Mage::helper('mageshop_pagarme')->getUrlWebHook()) . '" type="button">Copiar URL</button>
            </div>
            <style>
                div.url-box:before {
                    content: "";
                    width: 3px;
                    height: 100%;
                    background: #4caf50;
                    position: absolute;
                    left: 0px;
                    display: block;
                }
                div#url-box {
                    position: relative;
                }
                div#url-box {
                    background: #4caf5021;
                    max-width: 330px;
                }
                span#url-span {
                    display: block;
                    width: 100%;
                    font-size: 10px;
                    color: #ffffff;
                    font-weight: 600;
                    background: #4caf50;
                    border-radius: 0px;
                    margin: auto;
                    margin-top: 0px;
                    line-height: 20px;
                    text-align: center;
                }
            </style>
            <script>
                document.getElementById(\'botao-copiar\').addEventListener(\'click\', function() {
                    // Copia a URL para a área de transferência (pode ser adaptado conforme necessário)
                    var url = document.getElementById(\'botao-copiar\').value;
                    navigator.clipboard.writeText(url).then(function() {
                        alert(\'URL copiada com sucesso!\');
                    }).catch(function(err) {
                        alert(\'Erro ao copiar a URL: \', err);
                    });
                });
            </script>';
        return $html;
    }
}
