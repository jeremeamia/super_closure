<?php
/**
 * Super Closure Test Driver
 * 
 * @author		Jeremy Lindblom
 * @copyright	(c) 2010 Synapse Studios, LLC.
 */

require 'SuperClosure.class.php';

function strbool($bool)
{
	if (is_bool($bool))
		return $bool ? 'TRUE' : 'FALSE';
	else
		return $bool;
}

$domain = $_SERVER['HTTP_HOST'];
$protocol = 'http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 's' : '');

$closure = function ($relative_path, $port = FALSE) use ($domain, $protocol) {static $dummy = 10; return $protocol.'://'.$domain.($port ? ':'.$port : '').'/'.$relative_path;};

$SuperClosure = new SuperClosure($closure);

echo '<h1>SuperClosure Test</h1>';
echo '<p><strong>__invoke:</strong> '.$SuperClosure('blog/index').'</p>';
$closure = $SuperClosure->getClosure();
echo '<p><strong>getClosure (and invoke):</strong> '.$closure('blog/write').'</p>';
echo '<p><strong>Code:</strong> <code>'.$SuperClosure->getCode().'</code></p>';
echo '<strong>Parameters:</strong><ul>';
foreach ($SuperClosure->getParameters() as $param): ?>
	<li>
		<strong>Parameter #<?php echo $param->getPosition() + 1 ?></strong>
		<ul>
			<li><strong>Name:</strong> <?php echo $param->getName() ?></li>
			<li><strong>Position:</strong> <?php echo $param->getPosition() ?></li>
			<li><strong>Nullable?</strong> <?php echo strbool($param->allowsNull()) ?></li>
			<li><strong>Array?</strong> <?php echo strbool($param->isArray()) ?></li>
			<li><strong>Optional?</strong> <?php echo strbool($param->isOptional()) ?></li>
			<li><strong>Pass-by-Reference?</strong> <?php echo strbool($param->isPassedByReference()) ?></li>
			<li><strong>Default Value:</strong> <?php echo $param->isDefaultValueAvailable() ? strbool($param->getDefaultValue()) : NULL ?></li>
		</ul>
	</li>
<?php endforeach;
echo '</ul>';
echo '<strong>Used Variables:</strong><ul>';
foreach ($SuperClosure->getUsedVariables() as $key => $value): ?>
	<li><strong><?php echo $key ?>:</strong> <?php echo $value ?></li>
<?php endforeach;
echo '</ul>';
$serialized = serialize($SuperClosure);
$unserialized = unserialize($serialized);
echo '<p><strong>Post Serialization __invoke:</strong> '.$unserialized('blog/view').'</p>';