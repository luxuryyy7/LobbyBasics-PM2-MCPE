<?php
namespace LB;
use pocketmine\plugin\PluginBase as PB;
use pocketmine\command\{Command, CommandSender};
use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerJoinEvent, PlayerQuitEvent};
use pocketmine\Player;

class Main extends PB implements Listener {
private $latestRelease = null;
private $repo = "luxuryyy7/LobbyBasics-PM2-MCPE";

public function onEnable() {
$this->getServer()->getPluginManager()->registerEvents($this, $this);
$this->checkGithubRelease();
}

public function onDisable() { }

public function onCommand(CommandSender $p, Command $cmd, $lbl, array $args) {
$name = strtolower($cmd->getName());
switch($name) {
case "ad":
if(!$p instanceof Player) { $p->sendMessage("false"); return true; }
if(!($p->hasPermission("lb.ad") || $p->hasPermission("lb.op") || $p->isOp())) { $p->sendMessage("§cYou do not have permission to execute this command."); return true; }
if(!isset($args[0])) { return false; }
$msg = implode(" ", $args);
$send = $p->getName();
$this->getServer()->broadcastMessage("§r\n§f=======\n§r\n§7» §cAD §7|| §b{$send} §fsend an AD: §c{$msg}\n§r\n§f=======\n§r");
return true;

case "info":
$padd = str_repeat(" ", 10);
$fivePadd = str_repeat(" ", 5);
$msg = "\n{$padd}Your server\n";
$msg .= "\n{$fivePadd} Here you can add any information you want to your server. Simply duplicate this line multiple times if you want to add a lot of information, or use line jumps within a single line.\n";
$p->sendMessage($msg);
if(!($p instanceof \pocketmine\command\ConsoleCommandSender) && $p instanceof Player) { $p->sendPopup("Info sent in chat"); return true; }
return true;

case "helps":
$this->sendHelp($p, isset($args[0]) ? $args[0] : 1);
return true;

case "spawn":
if(!$p instanceof Player) { return false; }
$lvl = $p->getLevel();
$spawn = $lvl->getSafeSpawn();
$p->teleport($spawn);
$p->sendMessage("§aGoing to spawn");
return true;

case "rules":
if(!$p instanceof Player) { return false; }
$p->sendMessage("§l§c--- Server Rules ---");
$p->sendMessage("§f1. No cheating or hacks");
$p->sendMessage("§f2. Respect other players");
$p->sendMessage("§f3. No griefing or stealing");
$p->sendMessage("§f4. No spamming or advertising");
$p->sendMessage("§f5. Follow staff instructions");
return true;

case "colors":
if(!$p instanceof Player) { return false; }
if(!$p->hasPermission("lb.colors")) { $p->sendMessage("§cYou do not have permission to use this command."); return true; }
$p->sendMessage("§l§eAvailable color codes:");
$p->sendMessage("§a§a - Green§b§b - Aqua");
$p->sendMessage("§c§c - Red§d§d - Light Purple");
$p->sendMessage("§e§e - Yellow §f§f - White");
$p->sendMessage("§7§7 - Gray §8§8 - Dark Gray");
$p->sendMessage("§9§9 - Blue §2§2 - Dark Green");
return true;

case "lb":
$sub = isset($args[0]) ? strtolower($args[0]) : "help";
switch($sub) {
case "info":
$pd = $this->getDescription();
$name = $pd->getName();
$current = ltrim(trim($pd->getVersion()), "vV ");
$latest = ($this->latestRelease !== null) ? $this->latestRelease["tag"] : "N/A";
$status = "CURRENTLY: ";
if($this->latestRelease === null) {
$status .= "Updated";
} else {
$cv = ltrim(trim($pd->getVersion()), "vV ");
if(version_compare($latest, $cv, ">")) {
$status .= "Outdated";
} else {
$status .= "Updated";
}
}
$description = $pd->getDescription();
$author = implode(", ", (array) $pd->getAuthors());
$repo = $this->repo;
$p->sendMessage("§l§ePlugin Info: §r");
$p->sendMessage("§fName: §a{$name}");
$p->sendMessage("§fCurrent Version: §a{$current}");
$p->sendMessage("§fLatest Version: §a{$latest}");
$p->sendMessage("§f{$status}");
$p->sendMessage("§fDescription: §a{$description}");
$p->sendMessage("§fDeveloper: §a{$author}");
$p->sendMessage("§fRepo: §ahttps://github.com/{$repo}");
return true;

case "help":
default:
$p->sendMessage("§l§eLobbyBasics - Plugin Help§r");
$p->sendMessage("§f/helps §7- Show core help pages");
$p->sendMessage("§f/spawn §7- Teleport to spawn");
$p->sendMessage("§f/info §7- Server info text");
$p->sendMessage("§f/ad <msg> §7- Send AD (staff)");
$p->sendMessage("§f/rules §7- Show server rules");
$p->sendMessage("§f/colors §7- Show color codes (permission lb.colors)");
$p->sendMessage("§f/lb info §7- Plugin info and update status");
return true;
}
return true;

default:
return false;
}
}

public function onJoin(PlayerJoinEvent $event) {
$player = $event->getPlayer();
$pName = $player->getName();
$event->setJoinMessage("§bWelcome {$pName} to the server!");
if($this->latestRelease !== null) {
$player->sendMessage("§6[LobbyBasics] §eNew release available! Your version: §a" . $this->getDescription()->getVersion() . " §e| Latest: §a" . $this->latestRelease["tag"] . " §e| §b" . $this->latestRelease["url"]);
return;
}
$player->sendMessage("§6[LobbyBasics] §aYou are using the latest version: §f" . $this->getDescription()->getVersion());
}

public function onQuit(PlayerQuitEvent $ev) { $player = $ev->getPlayer(); $pName = $player->getName(); $ev->setQuitMessage("§c{$pName} has left the server!"); }

private function checkGithubRelease() {
$this->getLogger()->info("Finding new releases on GitHub...");
$repo = $this->repo;
$url = "https://api.github.com/repos/$repo/tags";
$userAgent = "LobbyBasicsPlugin";

if(!function_exists("curl_version")) {
$this->getLogger()->warning("cURL is not available, cannot check GitHub releases.");
return;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$json = @curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if($json === false || $httpCode >= 400) {
$this->getLogger()->warning("Could not fetch release info from GitHub (cURL). HTTP code: " . ($httpCode ?: "N/A") . " Error: " . $curlError);
return;
}

$tags = @json_decode($json, true);
if(!is_array($tags) || empty($tags)) {
$this->getLogger()->warning("Could not decode tags from GitHub (invalid JSON or empty).");
return;
}

$firstName = isset($tags[0]["name"]) ? $tags[0]["name"] : null;
if($firstName === null) {
$this->getLogger()->warning("No tags found on GitHub.");
return;
}

$latestTag = ltrim(trim($firstName), "vV ");
$currentVersion = ltrim(trim($this->getDescription()->getVersion()), "vV ");
$htmlUrl = "https://github.com/$repo/tree/" . $firstName;

if(version_compare($latestTag, $currentVersion, ">")) {
$this->getLogger()->info("New release found on GitHub! Your version: $currentVersion | New version: $latestTag | $htmlUrl");
$this->latestRelease = array("tag" => $latestTag, "url" => $htmlUrl);
} else {
$this->getLogger()->info("No new releases found, you are using the latest version! (Current: $currentVersion)");
$this->latestRelease = null;
}
}

private function sendHelp($sender, $page){
$pages = array();
$pages[1] = array(
"§l§e--- Help (1/1) ---§r",
"§a/helps §f→ See more cmds",
"§a/info §f→ See server info",
"§a/spawn §f→ Teleport to spawn",
"§a/rules §f→ Show server rules",
"§a/colors §f→ Show color codes (requires lb.colors)",
"§a/lb §f→ Plugin info & help"
);
$page = intval($page);
if($page < 1 || $page > count($pages)){
$sender->sendMessage("§cThat page doesn't exist. Use §e/help 1§c.");
return;
}
foreach($pages[$page] as $line){
$sender->sendMessage($line);
}
}
}
