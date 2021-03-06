<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Request;

use Temando\Shipping\Rest\Adapter\OrderApiInterface;
use Temando\Shipping\Rest\Request\Type\OrderRequestTypeInterface;

/**
 * Temando API Order Operation Parameters
 *
 * @package  Temando\Shipping\Rest
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class OrderRequest implements OrderRequestInterface
{
    /**
     * @var OrderRequestTypeInterface
     */
    private $order;

    /**
     * OrderRequest constructor.
     *
     * @param OrderRequestTypeInterface $order
     */
    public function __construct(OrderRequestTypeInterface $order)
    {
        $this->order = $order;
    }

    /**
     * @return string[]
     */
    public function getPathParams()
    {
        if (!$this->order->getId()) {
            return [];
        }

        return [
            $this->order->getId(),
        ];
    }

    /**
     * @param string $actionType
     * @return string[]
     */
    public function getRequestParams($actionType)
    {
        $requestParams = [];
        if (!$this->order->canPersist()) {
            $requestParams['persist'] = 'false';
        }

        if ($actionType === OrderApiInterface::ACTION_CREATE) {
            $requestParams['action'] = 'orderQualification';
        } elseif ($actionType === OrderApiInterface::ACTION_GET_COLLECTION_POINTS) {
            $requestParams['action'] = 'quoteCollectionPoints';
            $requestParams['experience'] = 'default';
        } elseif ($actionType === OrderApiInterface::ACTION_CREATE_PICKUP_ORDER) {
            $requestParams['action'] = 'orderQualificationClickAndCollect';
        } elseif ($actionType === OrderApiInterface::ACTION_ALLOCATE) {
            $requestParams['action'] = 'allocate';
            $requestParams['experience'] = $this->order->getSelectedExperienceCode();
        }

        return $requestParams;
    }

    /**
     * @return string
     */
    public function getRequestBody()
    {
        return json_encode($this->order, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
