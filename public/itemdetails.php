<?php
use App\Exceptions\ClassException;
use App\Lib\Logger;
use App\Models\Bid;
use App\Models\Item;
use App\Models\User;

require_once(__DIR__ . "/../app/bootstrap.php");

$validid = pf_validate_number($_GET['id'], "redirect", CONFIG_URL);

try {
    $item = Item::findFirst(["id" => "$validid"]);
} catch (ClassException $e) {
    Logger::getLogger()->critical("Invalid Item: ", ['exception' => $e]);
    echo "Invalid Item";
    die();
}

$item->getImage();
$item->getBids();

if (isset($_POST['submit'])) {
    if (is_numeric($_POST['bid']) == false) {
        header("Location: itemdetails.php?id=" . $validid . "&error=letter");
        die();
    }

    $validbid = false;

    if (count($item->get('bidObjs')) == 0) {
        $price = intval($item->get('price'));
        $postedBid = intval($_POST['bid']);

        if ($postedBid >= $price) {
            $validbid = true;
        }
    } else {
        $bids = $item->get('bidObjs');
        $highestBid = array_shift($bids);
        $highestBid = intval($highestBid->get('amount'));
        $postedBid = intval($_POST['bid']);
        if ($postedBid > $highestBid) {
            $validbid = true;
        }
    }

    if ($validbid == false) {
        header("Location: itemdetails.php?id=" . $validid . "&error=lowprice#bidbox");
        die();
    } else {
        $newBid = new Bid($item->get('id'), $_POST['bid'], $session->getUser()->get('id'));
        $newBid->create();
        header("Location: itemdetails.php?id=" . $validid);
        die();
    }
}

require_once(__DIR__ . "/../app/Layouts/header.php");

$nowepoch = time();
$itemepoch = strtotime($item->get('date'));

$validAuction = false;
if ($itemepoch > $nowepoch) {
    $validAuction = true;
}

echo "<h1>" . $item->get('name') . "</h1>";
echo "<p>";

if (count($item->get('bidObjs')) == 0) {
    echo "<strong>This item has had no bids</strong> - ";
    echo "<strong>Starting Price</strong>: " . CONFIG_CURRENCY . sprintf('%.2f', $item->get('price'));
} else {
    $bids = $item->get('bidObjs');
    $highestBid = array_shift($bids);
    echo "<strong>Number Of Bids</strong>: " . count($item->get('bidObjs')) . ". ";
    echo "<strong>Current Price</strong>: " . CONFIG_CURRENCY . sprintf('%.2f', $highestBid->get('amount'));
}

echo " - <strong>Auction ends</strong>: " . date("D jS F Y g.iA", $itemepoch);
echo "</p>";

$img = $item->get('imageObj');

if ($img) {
    echo "<img src='imgs/{$img->get('name')}' width='200'>";
} else {
    echo "No image.";
}

echo "<p>" . nl2br($item->get('description')) . "</p>";
echo "<a name='bidbox'></a>";
echo "<h2>Bid for this item</h2>";

if (!$session->isLoggedIn()) {
    echo "To bid, you need to log in. Login <a href='login.php?id=" . $validid . "&ref=addbid'>here</a>.";
} else {
    if ($validAuction == true) {
        echo "Enter the bid amount into the box below.";
        echo "<p>";

        if (isset($_GET['error'])) {
            try {
                $errorMsg = Item::displayError($_GET['error']);
            } catch (ClassException $e) {
                Logger::getLogger()->error("Invalid error code: ", ['exception' => $e]);
                die();
            }
            echo $errorMsg;
        }
        ?>
        <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
            <table>
                <tr>
                    <td><input type="number" name="bid"></td>
                    <td><input type="submit" name="submit" id="submit" value="Bid!"></td>
                </tr>
            </table>
        </form>
        <?php
    } else {
        echo "This auction has now ended.";
    }
}

if (count($item->get('bidObjs')) > 0) {
    echo "<h2>Bid History</h2>";
    echo "<ul>";

    $bids = $item->get('bidObjs');

    /* @var $bid \App\Models\Bid */
    foreach ($bids as $bid) {
        $id = $bid->get('user_id');
        try {
            $user = User::findFirst(["id" => "$id"]);
        } catch (ClassException $e) {
            Logger::getLogger()->critical("Invalid User: ", ['exception' => $e]);
            echo "Invalid User";
            die();
        }
        echo "<li>{$user->get('username')} - " . CONFIG_CURRENCY . sprintf('%.2f', $bid->get('amount')) . "</li>";
    }
    echo "</ul>";
}

require(__DIR__ . "/../app/Layouts/footer.php");
?>