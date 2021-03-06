<?php

namespace PhpSigep;

use PhpSigep\Cache\Options;
use PhpSigep\Cache\Storage\Adapter\AdapterOptions;
use PhpSigep\Cache\StorageInterface;
use PhpSigep\Model\AccessData;
use PhpSigep\Model\AccessDataHomologacao;

/**
 * @author: Stavarengo
 * @author: davidalves1
 */
class Config extends DefaultStdClass
{
    /**
     * Indica que estamos no ambiente real (ambiente de producao).
     */
    const ENV_PRODUCTION = 1;
    /**
     * Indica que estamos no ambiente de desenvolvimento.
     */
    const ENV_DEVELOPMENT = 2;

    const WSDL_ATENDE_CLIENTE_PRODUCTION = 'https://apps.correios.com.br/SigepMasterJPA/AtendeClienteService/AtendeCliente?wsdl';

    const WSDL_ATENDE_CLIENTE_DEVELOPMENT = 'https://apphom.correios.com.br/SigepMasterJPA/AtendeClienteService/AtendeCliente?wsdl';

    /**
     * Url do ambiente de produção (real) da logistica reversa.
     * @var
     */
    const WSDL_LOGISTICA_REVERSA_PRODUCTION = 'https://cws.correios.com.br/logisticaReversaWS/logisticaReversaService/logisticaReversaWS?wsdl';
    //https://cws.correios.com.br/logisticaReversaWS/logisticaReversaService/logisticaReversaWS?wsdl
//    const WSDL_LOGISTICA_REVERSA_PRODUCTION = 'https://s3.amazonaws.com/send4public/AtendeCliente.xml?wsdl';
//    const WSDL_LOGISTICA_REVERSA_PRODUCTION = 'https://s3.amazonaws.com/send4public/correio-reverso.wsdl';
    /**
     * Url do ambiente de homologação da logistica reversa.
     * @var
     */
    const WSDL_LOGISTICA_REVERSA_DEVELOPMENT = 'https://apphom.correios.com.br/logisticaReversaWS/logisticaReversaService/logisticaReversaWS?wsdl';

    const WSDL_CAL_PRECO_PRAZO = 'http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx?WSDL';

    const WSDL_RASTREAR_OBJETOS = 'https://webservice.correios.com.br/service/rastro/Rastro.wsdl';

    /**
     * Endereço para o WSDL AtendeCliente.
     * Esse WSDL possui duas versões, uma para o ambiente de produção e outra para o ambiente de desenvolvimento.
     * @var string
     */
    protected $wsdlAtendeCliente = self::WSDL_ATENDE_CLIENTE_DEVELOPMENT;

    protected $wsdlLogisticaReversa = self::WSDL_LOGISTICA_REVERSA_PRODUCTION;

    /**
     * @var string
     */
    protected $wsdlCalPrecoPrazo = self::WSDL_CAL_PRECO_PRAZO;

    /**
     * @var string
     */
    protected $wsdlRastrearObjetos = self::WSDL_RASTREAR_OBJETOS;

    /**
     * @var int
     */
    protected $env = self::ENV_DEVELOPMENT;

    /**
     * @var bool
     */
    protected $simular = false;

    /**
     * @var AdapterOptions
     */
    protected $cacheOptions = null;

    /**
     * Fábrica que irá criar e retornar uma instância de {@link \PhpSigep\Cache\StorageInterface }
     * @var string|FactoryInterface
     */
    protected $cacheFactory = 'PhpSigep\Cache\Factory';

    /**
     * @var StorageInterface
     */
    protected $cacheInstance;

    /**
     * Muitos dos métodos do php-sigep recebem como parâmetro uma instância de {@link AccessData}, mas você não precisa
     * passar essa informação todas as vezes que for pedido.
     * O valor setado neste atributo será usado sempre que um método precisar dos dados de acesso mas você não tiver
     * informado um.
     *
     * @var AccessData
     */
    protected $accessData;
    /**
     * @var bool
     */
    private $logisticaReversa = false;

    /**
     * @var bool
     */
    private $useCURLClient = false;

    /**
     * @param array $configData
     *      Qualquer atributo desta classe pode ser usado como uma chave deste array.
     *      Ex: array('cacheOptions' => ...)
     */
    public function __construct(array $configData = [])
    {
        $this->setAccessData(new AccessDataHomologacao());

        parent::__construct($configData);
    }

    /**
     * Não defina env como true em ambiente de produção.
     * @return bool
     */
    public function getEnv()
    {
        return (int)$this->env;
    }

    /**
     * @param int $env
     * @param bool $updateWsdlUrl
     * @return $this
     */
    public function setEnv($env, $updateWsdlUrl = true)
    {
        $this->env = $env;

//        if ($env == self::WSDL_RASTREAR_OBJETOS){
//            $this->setWsdlRastrearObjetos(self::WSDL_RASTREAR_OBJETOS);
//        }

        if ($updateWsdlUrl) {

            /**
             * Logística reversa.
             */
            if ($this->getLogisticaReversa() == true) {
                if ($env == self::ENV_DEVELOPMENT) {
                    $this->setWsdlLogisticaReversa(self::WSDL_LOGISTICA_REVERSA_DEVELOPMENT);
                } else {
                    $this->setWsdlLogisticaReversa(self::WSDL_LOGISTICA_REVERSA_PRODUCTION);
                }
            } else {
                if ($env == self::ENV_DEVELOPMENT) {
                    $this->setWsdlAtendeCliente(self::WSDL_ATENDE_CLIENTE_DEVELOPMENT);
                } else {
                    $this->setWsdlAtendeCliente(self::WSDL_ATENDE_CLIENTE_PRODUCTION);
                }
            }

        }

        return $this;
    }

    /**
     *
     * @return bool
     */
    public function getLogisticaReversa()
    {
        return $this->logisticaReversa;
    }

    /**
     * Define se a requisição é para logistica reversa.
     * @access public
     * @param bool $logisticaReversa
     * @return Config
     */
    public function setLogisticaReversa($logisticaReversa)
    {
        $this->logisticaReversa = $logisticaReversa;
        return $this;
    }

    /**
     * @return \PhpSigep\Model\AccessData
     */
    public function getAccessData()
    {
        return $this->accessData;
    }

    /**
     * @param \PhpSigep\Model\AccessData $accessData
     * @return $this;
     */
    public function setAccessData(\PhpSigep\Model\AccessData $accessData)
    {
        $this->accessData = $accessData;

        return $this;
    }

    public function getWsdlLogisticaReversa()
    {
        return $this->wsdlLogisticaReversa;
    }

    /**
     * @param string $wsdlLogisticaReversa
     * @return $this
     */
    public function setWsdlLogisticaReversa($wsdlLogisticaReversa)
    {
        $this->wsdlLogisticaReversa = $wsdlLogisticaReversa;

        return $this;
    }

    /**
     * @return string
     */
    public function getWsdlAtendeCliente()
    {
        return $this->wsdlAtendeCliente;
    }

    /**
     * @param string $wsdlAtendeCliente
     * @return $this
     */
    public function setWsdlAtendeCliente($wsdlAtendeCliente)
    {
        $this->wsdlAtendeCliente = $wsdlAtendeCliente;

        return $this;
    }

    /**
     * @param string $wsdlCalPrecoPrazo
     * @return $this;
     */
    public function setWsdlCalPrecoPrazo($wsdlCalPrecoPrazo)
    {
        $this->wsdlCalPrecoPrazo = $wsdlCalPrecoPrazo;

        return $this;
    }

    /**
     * @return string
     */
    public function getWsdlCalcPrecoPrazo()
    {
        return $this->wsdlCalPrecoPrazo;
    }

    /**
     * @return string
     */
    public function getWsdlRastrearObjetos()
    {
        return $this->wsdlRastrearObjetos;
    }

    /**
     * @param $wsdlRastrearObjetos
     * @return $this
     */
    public function setWsdlRastrearObjetos($wsdlRastrearObjetos)
    {
        $this->wsdlRastrearObjetos = $wsdlRastrearObjetos;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getSimular()
    {
        return $this->simular;
    }

    /**
     * @param boolean $simular
     */
    public function setSimular($simular)
    {
        $this->simular = $simular;
    }

    /**
     * @return Options
     */
    public function getCacheOptions()
    {
        if ($this->cacheOptions === null) {
            $this->setCacheOptions(new Options());
        }

        return $this->cacheOptions;
    }

    /**
     * @param array|\PhpSigep\Cache\Options $cacheOptions
     */
    public function setCacheOptions($cacheOptions)
    {
        if (!($cacheOptions instanceof Options)) {
            $cacheOptions = new Options($cacheOptions);
        }
        $this->cacheOptions = $cacheOptions;
    }

    /**
     * Este não é o melhor lugar para este método, mas dada a simplicidade do projeto ele pode ficar aqui por enquanto.
     * @todo Criar um Service Manager para abstrair a criação dos objetos.
     */
    public function getCacheInstance()
    {
        if (!$this->cacheInstance) {
            $factory = $this->getCacheFactory();
            $this->cacheInstance = $factory->createService($this);
        }

        return $this->cacheInstance;
    }

    /**
     * @return \PhpSigep\FactoryInterface
     */
    public function getCacheFactory()
    {
        $this->setCacheFactory($this->cacheFactory);

        return $this->cacheFactory;
    }

    /**
     * @param string|FactoryInterface $cacheFactory
     * @throws InvalidArgument
     */
    public function setCacheFactory($cacheFactory)
    {
        if ($cacheFactory != $this->cacheFactory || !($cacheFactory instanceof FactoryInterface)) {
            if (is_string($cacheFactory)) {
                $cacheFactory = new $cacheFactory;
            }
            if (!$cacheFactory || !($cacheFactory instanceof FactoryInterface)) {
                throw new InvalidArgument('O cacheFactory deve implementar PhpSigep\FactoryInterface.');
            }

            $this->cacheFactory = $cacheFactory;
        }
    }

    public function setUseCURLClient($useCURLClient)
    {
        $this->useCURLClient = $useCURLClient;

    }

    public function useCURLClient()
    {
        return function_exists('curl_init') && $this->useCURLClient;
    }
}
