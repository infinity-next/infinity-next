<?php

namespace App;

use App\Traits\EloquentBinary;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Dice.
 *
 * Yeah, like a board game dice.
 *
 * @category   Model
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class Dice extends Model
{
    use EloquentBinary;

    /**
     * Pattern to match a die.
     *
     * @var regex
     */
    const PATTERN = '(100|[1-9][0-9]{0,4})';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'dice';

    /**
     * The primary key that is used by ::get().
     *
     * @var string
     */
    protected $primaryKey = 'dice_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rolling',
        'sides',
        'modifier',
        'greater_than',
        'less_than',
        'minimum',
        'maximum',
        'rolls',
        'total',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'rolling' => 'integer',
        'rolls' => 'collection',
        'sides' => 'integer',
        'modifier' => 'integer',
        'total' => 'integer',
    ];

    /**
     * Determines if we're flipping a coin rather than rolling dice.
     *
     * @return bool
     */
    public function isBinary()
    {
        return $this->sides === 2;
    }

    /**
     * Determines if a number can be added to the total of a throw.
     *
     * @param  int  $roll
     *
     * @return bool
     */
    public function isCountedRoll($roll)
    {
        if (!is_null($this->greater_than) && $roll > $this->greater_than) {
            return false;
        }
        if (!is_null($this->less_than) && $roll < $this->less_than) {
            return false;
        }

        return true;
    }

    /**
     * Determines if a number can be counted towards number of times rolled.
     *
     * @param  int  $roll
     *
     * @return bool
     */
    public function isValidRoll($roll)
    {
        if (!is_null($this->minimum) && $roll <= $this->minumum) {
            return false;
        }
        if (!is_null($this->maximum) && $roll >= $this->maximum) {
            return false;
        }

        return true;
    }

    /**
     * Rolls a single die under conditions.
     *
     * @param  bool $autosave If the roll should be stored in `roles`.
     *
     * @return int
     */
    public function roll($autosave = true)
    {
        $roll = mt_rand(1, $this->sides);

        if ($autosave === true) {
            $this->rolls = $this->rolls->push($roll);
        }

        return $roll;
    }

    /**
     * Throws dice satisfy conditions.
     *
     * @static
     *
     * @param  integer $rolling            Dice to roll. Defaults 1.
     * @param  integer $sides              Sides of the dice. Defaults 6.
     * @param  integer $modifier           Value to add to / take from the net. Defaults 0.
     * @param  integer|null $greater_than  Minimum value to count. Ignores <=. Defaults null.
     * @param  integer|null $less_than     Maximum value to count. Ignores >=. Defaults null.
     * @param  integer|null $minimum       Minimum value to accept. Rerolls <=. Defaults null.
     * @param  integer|null $maximum       Maximum value to accept. Rerolls >=. Defaults null.
     *
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public static function throw($rolling = 1, $sides = 6, $modifier = 0, $greater_than = null, $less_than = null, $minimum = null, $maximum = null)
    {
        // turns "+100" to 100 and "-100" to -100.
        $modifier = (int) $modifier;

        if ($rolling < 1) {
            throw new InvalidArgumentException('Rolling fewer than 1 die.');
        }
        if ($sides < 2) {
            throw new InvalidArgumentException('Rolling dice with fewer than 2 sides.');
        }
        if (!is_null($minimum)) {
            if ($minimum > $sides) {
                throw new InvalidArgumentException('Rolling for minimum smaller than number of sides.');
            }
            if (!is_null($maximum) && $minimum > $maximum) {
                throw new InvalidArgumentException('Rolling for minimum larger than maximum roll.');
            }
        }
        if (!is_null($maximum) && $maximum < 2) {
            throw new InvalidArgumentException('Maximum is smaller than 2.');
        }
        if ($sides > 100 && (!is_null($minimum) || !is_null($maximum))) {
            throw new InvalidArgumentException('Probablistic dice rolls cannot be done on a die exceeding 100 sides. '
                .'<small>I\'m not running a bitcoin miner.</small>');
        }

        $dice = new static([
            'rolling' => $rolling,
            'sides' => $sides,
            'modifier' => $modifier,
            'greater_than' => $greater_than,
            'less_than' => $less_than,
            'minimum' => $minimum,
            'maximum' => $maximum,
            'rolls' => collect(),
            'total' => 0,
        ]);

        for ($i = 0; $i < $rolling; $i) {
            $roll = $dice->roll();

            if (!$dice->isValidRoll($roll)) {
                continue;
            }

            ++$i;

            if (!$dice->isCountedRoll($roll)) {
                continue;
            }

            $dice->total += $roll;
        }

        $dice->total += $modifier;
        return $dice;
    }

    public function toHtml()
    {
        return view('widgets.dice', [
            'dice' => $this,
        ]);
    }
}
