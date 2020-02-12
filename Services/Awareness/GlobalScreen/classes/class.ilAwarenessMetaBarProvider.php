<?php


use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\UI\Implementation\Component\Button\Bulky;


/**
 * Who-Is-Online meta bar provider
 *
 * @author <killing@leifos.de>
 */
class ilAwarenessMetaBarProvider extends AbstractStaticMetaBarProvider implements StaticMetaBarProvider
{

    /**
     * @return IdentificationInterface
     */
    private function getId() : IdentificationInterface
    {
        return $this->if->identifier('awareness');
    }


    /**
     * @inheritDoc
     */
    public function getAllIdentifications() : array
    {
        return [$this->getId()];
    }


    /**
     * @inheritDoc
     */
    public function getMetaBarItems() : array
    {
        global $DIC;

        $gui = new ilAwarenessGUI();
        $result = $gui->getAwarenessList(true);

        $content = function () use ($result) {
            return $this->dic->ui()->factory()->legacy($result["html"]);
        };

        $mb = $this->globalScreen()->metaBar();

        $f = $DIC->ui()->factory();

        $online = explode(":", $result["cnt"]);
        $online = $online[0];

        $item = $mb
            ->topLegacyItem($this->getId())
            ->addComponentDecorator(static function (ILIAS\UI\Component\Component $c) : ILIAS\UI\Component\Component {
                if ($c instanceof Bulky) {
                    return $c->withAdditionalOnLoadCode(static function (string $id) : string {

                        // ...we never get the bulky button of the legacy slate item here
                        return "$('#$id').on('click', function() {
                                    console.log('click slate button');
                                })";
                    });
                }
                return $c;
            })
            ->withLegacyContent($content())
            ->withSymbol(
                $this->dic->ui()->factory()
                ->symbol()
                ->glyph()
                ->user()
                ->withCounter($f->counter()->status((int) $online))
            )
            ->withTitle("Who is online")
            ->withPosition(2)
            ->withAvailableCallable(
                function () use ($DIC, $online) {
                    $ilUser = $DIC->user();

                    $awrn_set = new ilSetting("awrn");
                    if ($online <= 0 || !$awrn_set->get("awrn_enabled", false) || ANONYMOUS_USER_ID == $ilUser->getId()) {
                        return false;
                    }
                    return true;
                }
            );

        return [$item];
    }
}
