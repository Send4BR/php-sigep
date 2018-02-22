<?php

namespace PhpSigep\Model;

/**
 *
 * @author William Novak <williamnvk@gmail.com>
 */

class SolicitacaoDePostagemReversaRetorno extends AbstractModel
{
    /**
     * @var object
     */
    protected $numeroColeta;

    /**
     * @param int $numeroColeta
     * @return $this;
     */
    public function setNumeroColeta($numeroColeta)
    {
        $this->numeroColeta = $numeroColeta;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumeroColeta()
    {
        return $this->numeroColeta;
    }

}
