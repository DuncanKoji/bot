<?php /* just for fun */ ?>
<!DOCTYPE html>
<html lang="en" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<script>
    // var dynamicContentRoot = 'staging-new.shellshock.io';
    // var dynamicContentPrefix = 'https://staging-new.shellshock.io/';
    var dynamicContentRoot = '';
    var dynamicContentPrefix = '';
	var gtmOptions = { cookie_flags: 'secure;samesite=none' };
</script>

<script>
class Loader {
	static show () {
		let container = document.createElement('div');
		container.id = 'progress-container';
		container.style = `
			position: fixed;
			top: 0;
			left: 0;
			height: 100vh;
			width: 100vw;
			z-index: 2000;
			background-image: var(--ss-lightoverlay);
		`;

		const progressWrapper = document.createElement('div');
		progressWrapper.id = 'progress-wrapper';
		progressWrapper.className = 'load_screen align-items-center';
		progressWrapper.style = `
			position: absolute;
			left: 50%;
			top: -6em;
    		transform: translateX(-50%);
			background-image: none;
		`;

		const blueWizLogo = document.createElement('img');
		blueWizLogo.src = 'img/BlueWizard-Logo-min.png';
		blueWizLogo.style=`
			width: 16em;
			display: block;
			margin: 5em auto 0;
			z-index: 2000;
			position: absolute;
			left: 50%;
			bottom: 8em;
    		transform: translateX(-50%);
		`;

		let logo = document.createElement('img');
		logo.src = 'img/logo.svg';
		logo.style = 'height: 16em';
		logo.id = 'logo-svg';
		// container.appendChild(logo);

		const progressOuter = document.createElement('div');
		progressOuter.id = 'progress-outer';
		progressOuter.style = `
			position: relative;
			background: #643219;
			border-radius: 2em;
			height: 3.3em;
			width: 24em;
			margin-top: 2em;
		`;


		let progress = document.createElement('div');
		progress.style = `
			margin-top: 1em;
			width: 23em;
			height: 2.2em;
			background: white;
			padding: 0.5em;
			border-radius: 2em;
    		margin: .3em .5em 0;
		`;
		container.appendChild(progress);

		let progressBar = document.createElement('span');
		progressBar.id = 'progressBar';
		progressBar.style = `
			display: block;
			width: 20%;
			height: 100%;
			background: orange;
			border-radius: 2em;
			margin-left: 80%;
			margin: 0 .3em .5em 0;
			opacity: 0;
			transition: margin-left linear 500ms;
			transition-timing-function: ease-in-out;
		`;

		const progressBarOutside = document.createElement('div');


		progress.appendChild(progressBar);
		progressWrapper.appendChild(logo);
		progressOuter.appendChild(progress);
		progressWrapper.appendChild(progressOuter);
		
		container.appendChild(progressWrapper);
		container.appendChild(blueWizLogo);

		// Minor for the progress bar intial load
		setTimeout(() => progressBar.style.opacity = 1, 600);


		Loader.barInterval = setInterval(() => {
			if (Loader.progressBar.style.marginLeft == '0%') {
				Loader.progressBar.style.marginLeft = '80%';
			}
			else {
				Loader.progressBar.style.marginLeft = '0%';
			}
		}, 500);

		Loader.progressBar = progressBar;
		Loader.container = container;

		let app = document.body;
		app.appendChild(container);
	}

	static hide () {
		Loader.container.style = "opacity : 0; transition: opacity 1s;";
		setTimeout(() => { Loader.container.remove(); }, 1000);
	}

	static addTask () {
		let id = Loader.loaded.length;
		//console.log('Loading tasks: ', ++Loader.actualTasks);
		Loader.loaded.push(0);
		return id;
	}

	static finish (id) {
		clearInterval(Loader.barInterval);

		if (Loader.progressBar) {
			Loader.progressBar.style.marginLeft = '0%';
			Loader.progressBar.style.transition = '';

			Loader.loaded[id] = 1;
			Loader.updateBar();
		}
	}

	static progress (id, value, total) {
		clearInterval(Loader.barInterval);

		if (Loader.progressBar) {
			Loader.progressBar.style.marginLeft = '0%';
			Loader.progressBar.style.transition = '';

			Loader.loaded[id] = value / total;

			Loader.updateBar();
		}

		return id;
	}

	static updateBar () {
		let loadedTotal = 0;

		for (let l of Loader.loaded) {
			loadedTotal += l;
		}

		Loader.progressBar.style.width = loadedTotal / Loader.tasks * 95 + 5 + '%';
	}

	static loadJS (path, callback) {
		let p = path;

		(function (p, cb) {
			let xhr = new XMLHttpRequest();
  			xhr.open('GET', p, true);

			let id = Loader.addTask();

			xhr.onprogress = event => {
				if (Loader.progressBar) {
					id = Loader.progress(id, event.loaded, event.total);
				}
			};
  
			xhr.onload = () => {
				if (xhr.status != 200) {
      				console.log(`Error ${xhr.status}: ${xhr.statusText}`);
    			}
				else {
					Loader.finish(id);

					let script = document.createElement('script');
					script.innerHTML = xhr.response;
					document.body.appendChild(script);

					if (cb) cb();
				}
			};

			xhr.send();
		})(path, callback);
	}
}

Loader.actualTasks = 0;
Loader.tasks = 17;
Loader.loaded = [];

window.Loader = Loader;
window.indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB;

function openFirebaseDb () {
    return new Promise((resolve, reject) => {
        let req = window.indexedDB.open('firebaseLocalStorageDb');
        req.onsuccess = () => {
			let db = req.result;
			let transaction = db.transaction(['firebaseLocalStorage'], 'readwrite');
			let store = transaction.objectStore('firebaseLocalStorage');
			resolve({ db, store });
		}
		req.onerror = err => reject(err);
		req.onupgradeneeded = () => {
			let db = req.result;
			let store = db.createObjectStore('firebaseLocalStorage', { keyPath: 'fbase_key' });
			resolve({ db, store });
		}
    });
}

var redirectIframe

function postStorageAndRedirect (iframe, storage, firebaseDb) {
	iframe.contentWindow.postMessage({ storage, firebaseDb }, '*');
	window.location = 'https://shellshock.io' + window.location.search + window.location.hash;
}

window.addEventListener('DOMContentLoaded', () => {
	Loader.show();
});
</script><!-- title, seo meta and favicons -->
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="facebook-domain-verification" content="6lfua33vx0abiv1asnt9p13aac29xy" />
<!-- <link rel="manifest" href="manifest.json"> -->
<title>Shell Shockers | geometry.monster</title>
<meta name="Description" content="The OFFICIAL home of Shell Shockers, the world's most advanced egg-based multiplayer shooter! It's like your favorite battlefield game, but...with eggs. URL Blocked? Try geometry.monster">
<meta name="Keywords" content="Play, Free, Online, Multiplayer, Games, IO, ShellShockers, Shooter, Bullets, Top Down">
<meta name="author" content="Blue Wizard Digital">

<meta name="theme-color" content="#0B93BD" />
<meta name="background-color" content="#0B93BD" />

<link rel="icon" href="favicon.ico" type="image/x-icon">
<link rel="apple-touch-icon" href="favicon192.png" sizes="192x192" />
<link rel="icon" href="favicon256.png" sizes="512x512" />

<meta property="og:url"                content="https://www.shellshock.io" />
<meta property="og:type"               content="website" />
<meta property="og:image:width"        content="1000" />
<meta property="og:image:height"	   content="500" />
<meta property="og:image"              content="https://www.shellshock.io/img/previewImage_shellShockers.jpg" />
<meta name="image" property="og:image" content="https://www.shellshock.io/img/previewImage_shellShockers.jpg" />
<meta property="og:title"              content="Shell Shockers | by Blue Wizard Digital" />
<meta property="og:description"        content="The OFFICIAL home of Shell Shockers, the world's most advanced egg-based multiplayer shooter! It's like your favorite battlefield game, but...with eggs. URL Blocked? Try geometry.monster" />

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@eggcombat">
<meta name="twitter:creator" content="@eggcombat">
<meta name="twitter:title" content="Shell Shockers | by Blue Wizard Digital">
<meta name="twitter:description" content="The OFFICIAL home of Shell Shockers, the world's most advanced egg-based multiplayer shooter! It's like your favorite battlefield game, but...with eggs. URL Blocked? Try geometry.monster">
<meta name="twitter:image" content="https://www.shellshock.io/img/previewImage_shellShockers.jpg">
<!-- Styles & Fonts -->
<link href="https://fonts.googleapis.com/css?family=Sigmar+One|Nunito:100,200,600,700,900" rel="stylesheet">
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous"> -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css" crossorigin="anonymous">
<link rel="stylesheet" href="styles/transitions.css?1676508401">
<link rel="stylesheet" href="styles/forms.css?1677111783">
<link rel="stylesheet" href="styles/style.css?1677111783">
<link rel="stylesheet" href="styles/game.css?1677608275"><script>
function storageFactory(getStorage) {

	const inMemoryStorage = {};

	function isSupported() {
		try {
			var testKey = "__some_random_key_you_are_not_going_to_use__";
			getStorage().setItem(testKey, testKey);
			getStorage().removeItem(testKey);
			return true;
		} catch (e) {
			return false;
		}
	}

	function clear() {
		if (isSupported()) {
			getStorage().clear();
		} else {
			inMemoryStorage = {};
		}
	}

	function getItem(name) {
		if (isSupported()) {
			return getStorage().getItem(name);
		}

		if (inMemoryStorage.hasOwnProperty(name)) {
			return inMemoryStorage[name];
		}

		return null;
	}

	function key(index) {
		if (isSupported()) {
			return getStorage().key(index);
		} else {
			return Object.keys(inMemoryStorage)[index] || null;
		}
	}

	function removeItem(name) {
		if (isSupported()) {
			getStorage().removeItem(name);
		} else {
			delete inMemoryStorage[name];
		}
	}

	function setItem(name, value) {
		if (isSupported()) {
			getStorage().setItem(name, value);
		} else {
			inMemoryStorage[name] = String(value);
		}
	}

	function length() {
		if (isSupported()) {
			return getStorage().length;
		} else {
			return Object.keys(inMemoryStorage).length;
		}
	}

	return {
		getItem: getItem,
		setItem: setItem,
		removeItem: removeItem,
		clear: clear,
		key: key,

		get length() {
			return length();
		}

	};
	}

	const localStore = storageFactory(() => localStorage);
	const sessionStore = storageFactory(() => sessionStorage);
</script><style>
.eggIcon {
	display: inline-block;
	color: #444444;
	width: .8em;
	height: .8em;
	fill: currentColor;
}
</style>
						

<style>
.eggIconLocked {
	display: inline-block;
	color: #444444;
	width: .8em;
	height: .8em;
	fill: currentColor;
}
</style>
							
<svg style="position: absolute; width: 0; height: 0; overflow: hidden" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
	<defs>
		<symbol id="icon-egg-locked" viewBox="0 0 14.59 18.12">
<g>
	<path class="st0" d="M7.3,5.4c-0.6,0-1.1,0.5-1.1,1.1v1.3h2.2V6.5C8.4,5.9,7.9,5.4,7.3,5.4z"/>
	<path class="st0" d="M7.5,0.1c-4,0-7.4,6.7-7.4,10.7S3.4,18,7.3,18c3.9,0,7.2-3.2,7.2-7.2S11.5,0.1,7.5,0.1z M11.3,12.5
		c0,0.9-0.7,1.6-1.6,1.6H4.8c-0.9,0-1.6-0.7-1.6-1.6V7.8h1.5V6.5C4.8,5.1,5.9,4,7.3,4c1.4,0,2.5,1.1,2.5,2.5v1.3h1.5V12.5z"/>
</g>
		</symbol>
	</defs>
</svg><!-- ParsedURL -->
<script>
    var parsedUrl = (function parseUrl () {
        var url = {};
        var loc = window.location;
        url.root = loc.origin + loc.pathname;
        var query = loc.search.substring(1).split('&');
        url.query = {};
        for (let i = 0; i < query.length; i++)  {
            var arr = query[i].split('=');
            if (arr[0]) {
                if (arr[1] === undefined) {
                    arr[1] = true;
                } else if (!isNaN(arr[1])) {
                    arr[1] = parseFloat(arr[1]);
                }
                url.query[arr[0]] = arr[1];
            }
        }
        url.hash = loc.hash.substring(1);

        var host = loc.host.split('.');

        url.dom = host[0];
        url.top = host[1];

        if (url.hash.length == 0) url.hash = undefined;
        return url;
    })();
</script>
<!-- third party globals -->
<script>
    // Third party globals
    var crazysdk = {inviteLink: function () {}},
        pokiActive = false,
        crazyGamesActive = false,
        thirdPartyAdblocker = false,
        testCrazy = false;
</script><!-- Crazy Games -->
<script src="https://sdk.crazygames.com/crazygames-sdk-v1.js"></script>
<script type="text/javascript">
	const crazyAdDetect = (e) => {
		if (e.hasAdblock) {
			thirdPartyAdblocker = true;
		}
	};
	// const crazyInitialized = (e) => {
	// 	console.log('INITIALIZED: ', e);
	// };
	
	if (window.CrazyGames && CrazyGames.CrazySDK) {

		const { CrazySDK } = window.CrazyGames;
		crazysdk = CrazySDK.getInstance(); //Getting the SDK
		
		crazysdk.addEventListener('bannerRendered', (e) => {
			console.log(`Banner for container ${e.containerId} has been rendered!`);
		});
		
		crazysdk.addEventListener('bannerError', (e) => {
			console.log(`Banner render error: ${e.error}`);

			if (e.containerId === 'shellshockers_respawn_banner_2_ad' || e.containerId === 'shellshockers_respawn_banner-new_ad') {
				// We only reset the timeout if both banners fail during the same request
				if (++vueData.cGrespawnBannerErrors >= 2) {
					vueData.cGrespawnBannerErrors = 0;

					if (vueData.cGrespawnBannerTimeout) {
						clearTimeout(vueData.cGrespawnBannerTimeout);
						vueData.cGrespawnBannerTimeout = null;
					}
				}
			}
		});
		
		// crazysdk.addEventListener('initialized', crazyInitialized);
		crazysdk.addEventListener('adblockDetectionExecuted', crazyAdDetect);

		crazysdk.init(); //Initializing the SDK, call as early as possible
	}

    if (parsedUrl.query.testCrazy) {
        testCrazy = true;
	}

</script><!-- European Union detection -->
<script>isFromEU = 0 ? true : false</script>
<!-- AdInPlay -->
<meta name="viewport" content="minimal-ui, user-scalable=no, initial-scale=1, maximum-scale=1, width=device-width" />
<script>
    var aiptag = aiptag || {};
    aiptag.cmd = aiptag.cmd || [];
    aiptag.cmd.display = aiptag.cmd.display || [];
    aiptag.cmd.player = aiptag.cmd.player || [];
</script>

        <script async src="//api.adinplay.com/libs/aiptag/pub/SSK/shellshock.io/tag.min.js"></script>
<!-- GTM -->
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-79NWRZXYCB"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-79NWRZXYCB', gtmOptions);
</script>

<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-K5MSJHJ');</script>
<!-- End Google Tag Manager --><!-- In house ads -->
<script>
    window.googletag = window.googletag || {cmd: []};
    let inHouseSlot;
    const slots = [];

    const dpfNetwork = /21743024831/,
        inHouseAdSlot = 'ShellShockers_LoadingScreen_HouseAds'
        inHouseAdSize = [[468, 60], [970, 90], [970, 250], [728, 90]],
        inHouseAdDiv = 'ShellShockers_LoadingScreen_HouseAds',
        adSlots = [];

    // Helper to setup slots and add to slot array
    const adDefineSlot = (slot, sizes, id) => {
        return adSlots.push([{slot, sizes, id}]);
    };

    // Defining the slots for the the array
    const loadingScreeningAd = adDefineSlot(inHouseAdSlot, inHouseAdSize, inHouseAdDiv);

    // Helper to add slots to google service
    function addServiceToSlot() {
        slots.forEach(slot => {
            slot.addService(googletag.pubads());
        });
    }

    // Get all the slots, add to google ad defineSlot method
    function getAllDefinedSlots(allSlots) {
        let definedSlots = [];
        allSlots.forEach(adSlot => {
            for (var i = 0, len = adSlot.length; i < len; i++) {
                slots.push(googletag.defineSlot(dpfNetwork + adSlot[i].slot, adSlot[i].sizes, adSlot[i].id));
            }
        })
        return addServiceToSlot(slots);
    }

    const gtagInHouseLoadingBannerIntialLoad = () => {
        if (typeof hasPoki !== 'undefined') {
            console.log('haspoki', typeof(hasPoki));
            return; 
        }
        googletag.cmd.push(function() {
            getAllDefinedSlots(adSlots);
            googletag.pubads().disableInitialLoad();
            googletag.enableServices();
        });
    };

    gtagInHouseLoadingBannerIntialLoad();

    const adRenderedEvent = () => {
        return googletag.pubads().addEventListener('slotRenderEnded', (event) => {
            vueApp.disaplyAdEventObject(event);
        });
    };

    const gtagInHouseLoadingBanner = () => {
        googletag.cmd.push(function() {
            googletag.pubads().refresh([slots[0]]);
            adRenderedEvent();
        });
    };

    const destroyInhouseAdForPaid = () => {
        googletag.destroySlots([slots[0]]);
    };

</script><!-- Firebase -->
<script src="https://www.gstatic.com/firebasejs/7.21.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/7.21.1/firebase-auth.js"></script>

<script src="https://www.gstatic.com/firebasejs/ui/4.6.1/firebase-ui-auth.js"></script>
<link type="text/css" rel="stylesheet" href="https://www.gstatic.com/firebasejs/ui/4.6.1/firebase-ui-auth.css" />
<!-- Facebook -->
<!-- Facebook Pixel Code -->
<script>
	!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
	n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
	n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
	t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
	document,'script','https://connect.facebook.net/en_US/fbevents.js');
	fbq('init', '771186996377132');
	fbq('track', 'PageView');
</script>
<noscript>
	<img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=771186996377132&ev=PageView&noscript=1"/>
</noscript>
<!-- DO NOT MODIFY -->
<!-- End Facebook Pixel Code -->
<!-- progressive web app -->
<!-- <button id="addToHomescreen" style="z-index:333;display: none; position:absolute; top:0px; right: 75%; cursor:pointer;" class="ss_button btn_yolk bevel_yolk">Add to your desktop!</button> -->

<script>
    let pwaBlockAds = false;

	// if ('serviceWorker' in navigator) {
    //             console.log("Will the service worker register?");
    //             navigator.serviceWorker.register('service-worker.js')
    //             .then(function(reg){
    //                     console.log("Yes, it did.");
    //             }).catch(function(err) {
    //                     console.log("No it didn't. This happened:", err)
    //             });
    // }

    if (window.matchMedia('(display-mode: standalone)').matches) { 
        pwaBlockAds = 'utm_source' in parsedUrl.query && parsedUrl.query.utm_source === 'homescreen';
	ga('send', 'event', 'pwa', 'desktop opened');
    }  
</script>	
<!-- Music audio tag -->
<audio id="theAudio" preload="metadata"></audio>
<!-- VueJS -->
<script src="./js/vue/vue.min.2.6.10.js"></script><!-- tools and varibles -->
<script>

	localStore.removeItem('brbTime');

	String.prototype.format = String.prototype.f = function() {
    var s = this,
        i = arguments.length;

    while (i--) {
        s = s.replace(new RegExp('\\{' + i + '\\}', 'gm'), arguments[i]);
    }
    return s;
};

function getKeyByValue (obj, value) {
	// if (!obj && !value) {
	// 	return;
	// }
	for (var prop in obj) {
		if (obj.hasOwnProperty(prop)) {
			if (obj[prop] === value) {
				return prop;
			}
		}
	}
}

function objToStr (obj) {
	var str = JSON.stringify(obj, null, 4).replace(/\\|"/g, '');
	//str = str.replace(/\\|"/g, '');
	return str;
}

function detectChromebook() {
	return /\bCrOS\b/.test(navigator.userAgent);
}

function removeChildNodes (name) {
	var myNode = document.getElementById(name);
	while (myNode.firstChild) {
	    myNode.removeChild(myNode.firstChild);
	}
}

function logCallStack() {
	var stack = new Error().stack;
	console.log(stack);
}

function getRequest (url, callback) {
	if (url.startsWith('./')) url = url.slice(2);
	url = dynamicContentPrefix + url;

	var req = new XMLHttpRequest();
	if (!req) {
		return false;
	}

	if (typeof callback != 'function') callback = function () {};
	
	req.onreadystatechange = function(){
		if(req.readyState == 4) {
			return req.status === 200 ? 
				callback(null, req.responseText) : callback(req.status, null);
		}
	}
	req.open("GET", url, true);
	req.send(null);
	return req;
}

function hasValue (a) {
	return (a !== undefined && a !== null && a !== 0);
}

Array.prototype.shallowClone = function() {
	return this.slice(0);
}

function deepClone (o) {
	return JSON.parse(JSON.stringify(o));
}

function isString (value) {
	return typeof value === 'string' || value instanceof String;
}

const capitalize = (s) => {
	if (typeof s !== 'string') return ''
	return s.charAt(0).toUpperCase() + s.slice(1)
};

function isHttps() {
    return (document.location.protocol == 'https:');
}

function elOverlap(el1, el2) {
	const domRect1 = el1.getBoundingClientRect();
	const domRect2 = el2.getBoundingClientRect();
  
	return !(
	  domRect1.top > domRect2.bottom ||
	  domRect1.right < domRect2.left ||
	  domRect1.bottom < domRect2.top ||
	  domRect1.left > domRect2.right
	);
  }	
function getStoredNumber (name, def) {
	var num = localStore.getItem(name);
	if (!num) {
		return def;
	}
	return Number(num);
}

function getStoredBool (name, def) {
	var str = localStore.getItem(name);
	if (!str) {
		return def;
	}
	return str == 'true' ? true : false;
}

function getStoredString (name, def) {
	var str = localStore.getItem(name);
	if (!str) {
		return def;
	}
	return str;
}

function getStoredObject (name, def) {
	var str = localStore.getItem(name);
	if (!str) {
		return def;
	}
	return JSON.parse(str);
}
	var shellColors = [
	'#ffffff',
	'#c4e3e8',
	'#e2bc8b',
	'#d48e52',
	'#cb6d4b',
	'#8d3213',
	'#5e260f',

	'#e70a0a',
	'#aa24ce',
	'#f17ff9',
	'#FFD700',
	'#33a4ea',
	'#3e7753',
	'#59db27',
	//'#99953a'
];

var freeColors = shellColors.slice(0, 7);
var paidColors = shellColors.slice(7, shellColors.length);

	const RESPAWNADUNIT = 'shellshockers_respawn_banner-pr2';
	const RESPAWN2ADUNIT = 'shellshockers_respawn_banner_2-pr';
	const  AIPSUBID = 'proxy';

	var Slot = {
		Primary: 0,
		Secondary: 1
	};

	var EGGCOLOR = {
		white: 0,
		skyblue: 1,
		beige: 2,
		tan: 3,
		brown: 4,
		caramel: 5,
		chocolate: 6,
		red: 7,
		purple: 8,
		violet: 9,
		yellow: 10,
		babyblue: 11,
		darkgreen: 12,
		green: 13	
	}

	// Type matches contents of the item_type table (could be generated from a db query but ... meh)
	var ItemType = {
		Hat: 1,
		Stamp: 2,
		Primary: 3,
		Secondary: 4,
		Grenade: 6,
		Melee: 7
	}

	var CharClass = {
		Soldier: 0,
		Scrambler: 1,
		Ranger: 2,
		Eggsploder: 3,
		Whipper: 4,
		Crackshot: 5,
		TriHard: 6
	};

	const SOCIALMEDIA = [
		'fa-facebook-square',
		'fa-instagram-square',
		'fa-tiktok',
		'fa-discord',
		'fa-youtube',
		'fa-twitter-square',
		'fa-twitch'
	];

			/* Ranges
		Hat			1000 - 1999
		Stamp		2000 - 2999
		Secondary	3000 - 3099
		Soldier		3100 - 3399
		Range		3400 - 3599
		Scrambler	3600 - 3799
		Eggsploder	3800 - 3999
		Whipper		4000 - 4199
		Crackshot	4200 - 4499
		TriHard		4500 - < 16000
		Grenade		16000 - 16383
		*/
</script><!-- shellshockers js -->
<script>
	function ssJSComplete () {
		window.onloadingcomplete();
	}
	Loader.loadJS('js/shellshock.js?1677696600', ssJSComplete);
</script>
	
	</head>

	<body>
		<!-- google tag manager noscript -->
		<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-K5MSJHJ"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->		<svg width="0" height="0" style="position:absolute"><symbol viewBox="0 0 31.571 27.155" id="ico-backToGame" xmlns="http://www.w3.org/2000/svg"><path d="M2.057 26.506c-2.893-4.578-5.684-18.63 13.655-18.63h1.522V0L31.57 11.857 17.234 23.716V15.84h-4.35c-5.621 0-11.757 1.656-8.87 9.685.622 1.722-1.231 2.13-1.957.98Z"/></symbol><symbol xml:space="preserve" style="enable-background:new 0 0 24 19.9" viewBox="0 0 24 19.9" id="ico-checkmark" xmlns="http://www.w3.org/2000/svg"><path d="M10 18.2c-.2 0-.4-.1-.6-.2l-6.7-4.5c-.5-.3-.6-.9-.3-1.4l2.5-3.8c.2-.2.4-.4.7-.4h.1c.3 0 .6.1.8.3l3.4 3.1 6.8-9.1c.2-.2.5-.4.8-.4.3 0 .6.1.8.4l3.3 4c.3.4.3 1-.1 1.3L10.7 17.9c-.2.2-.5.3-.7.3z" style="fill:#f7ef1d"/><path d="m17.4 2.7 3.3 4L10 17.2l-6.7-4.5 2.5-3.8 4.2 3.8 7.4-10m0-2c-.7 0-1.2.3-1.6.8L9.7 9.8 7.1 7.4c-.3-.3-.8-.5-1.3-.5h-.2c-.6.1-1.2.4-1.5.9l-2.5 3.8c-.6.9-.4 2.2.5 2.8l6.7 4.5c.3.2.7.3 1.1.3.5 0 1-.2 1.4-.6L22.1 8.1c.7-.7.8-1.9.1-2.7l-3.3-4c-.3-.4-.9-.7-1.5-.7z" style="fill:#0c576f"/></symbol><symbol viewBox="0 0 18.703 34.574" id="ico-costDollar" xmlns="http://www.w3.org/2000/svg"><path d="M16.876 27.512c-1.219 1.391-2.86 2.306-4.923 2.744v1.793c0 .756-.214 1.366-.642 1.83s-.983.695-1.663.695-1.235-.232-1.663-.695-.642-1.074-.642-1.83v-1.646c-2.437-.341-4.522-1.134-6.256-2.379-.395-.268-.675-.572-.84-.914C.083 26.769 0 26.318 0 25.757c0-.732.192-1.366.577-1.903.383-.536.84-.805 1.366-.805.263 0 .527.05.79.146.264.098.604.281 1.02.55.966.584 1.867 1 2.701 1.243a9.456 9.456 0 0 0 2.667.366c1.163 0 2.047-.213 2.65-.64.605-.427.907-1.067.907-1.921 0-.512-.181-.933-.544-1.262-.362-.33-.813-.591-1.35-.787s-1.323-.439-2.354-.732c-1.646-.463-2.996-.932-4.05-1.408-1.054-.476-1.965-1.22-2.733-2.232C.877 15.36.494 14.001.494 12.293c0-1.952.614-3.646 1.844-5.086 1.23-1.439 2.854-2.39 4.874-2.853v-1.83c0-.732.219-1.335.658-1.81A2.147 2.147 0 0 1 9.516 0c.68 0 1.235.232 1.663.695s.642 1.073.642 1.83v1.72c1.822.316 3.6 1.084 5.334 2.304.396.292.675.616.84.969.165.354.247.787.247 1.3 0 .731-.198 1.366-.593 1.902s-.856.805-1.383.805a2 2 0 0 1-.757-.147c-.242-.097-.592-.28-1.053-.548a37.775 37.775 0 0 0-1.022-.604 8.013 8.013 0 0 0-1.695-.732 6.624 6.624 0 0 0-1.926-.274c-.988 0-1.79.238-2.404.713-.615.475-.922 1.116-.922 1.92 0 .782.35 1.367 1.054 1.757.702.39 1.81.781 3.325 1.17 1.625.416 2.958.855 4.001 1.318 1.042.463 1.943 1.207 2.7 2.232.757 1.024 1.136 2.402 1.136 4.134 0 1.976-.61 3.658-1.827 5.048Z"/></symbol><symbol viewBox="0 0 24.675 30.834" id="ico-costEgg" xmlns="http://www.w3.org/2000/svg"><path d="M12.612 0c6.814 0 12.063 12.114 12.063 18.774s-5.524 12.06-12.338 12.06S0 25.434 0 18.774 5.798 0 12.612 0Z"/></symbol><symbol viewBox="0 0 24 24" xml:space="preserve" id="ico-egg-w-outline" xmlns="http://www.w3.org/2000/svg"><path class="aeegg-fill" d="M12 21.6c-4.4 0-8-3.5-8-7.8 0-4 3.4-11.4 8.1-11.4 4.8 0 7.8 7.5 7.8 11.4.1 4.3-3.5 7.8-7.9 7.8z" style="fill-rule:evenodd;clip-rule:evenodd"/><path class="aeegg-stroke" d="M12.1 3.9c3.6 0 6.3 6.4 6.3 9.9s-2.9 6.3-6.5 6.3-6.5-2.8-6.5-6.3c.1-3.5 3.2-9.9 6.7-9.9m0-3C6.2.9 2.5 9.4 2.5 13.8c0 5.2 4.3 9.3 9.5 9.3s9.5-4.2 9.5-9.3C21.5 9.4 18.2.9 12.1.9z"/></symbol><symbol viewBox="0 0 35.313 35.313" id="ico-fullscreen" xmlns="http://www.w3.org/2000/svg"><path d="M31.243 0h-19.91a4.074 4.074 0 0 0-4.07 4.07v13.21H4.07A4.074 4.074 0 0 0 0 21.351v9.892a4.074 4.074 0 0 0 4.07 4.07h9.892a4.074 4.074 0 0 0 4.07-4.07V28.05h13.211a4.074 4.074 0 0 0 4.07-4.07V4.07A4.074 4.074 0 0 0 31.243 0ZM14.526 31.244c0 .31-.253.563-.564.563H4.07a.564.564 0 0 1-.564-.563V21.35c0-.31.253-.564.564-.564h3.193v3.194a4.074 4.074 0 0 0 4.07 4.07h3.193v3.193Zm17.28-7.263c0 .31-.252.563-.563.563h-19.91a.564.564 0 0 1-.564-.563V4.07c0-.31.253-.564.564-.564h19.91c.31 0 .564.253.564.564v19.91Z"/></symbol><symbol viewBox="0 0 30.281 36.454" id="ico-goldenEgg" xmlns="http://www.w3.org/2000/svg"><path d="M15.14 36.45c9.214.215 16.596-7.384 14.897-17.837C28.537 9.395 21.957 0 15.14 0S1.744 9.395.245 18.613c-1.7 10.453 5.682 18.052 14.896 17.837Z" style="fill:#f7941d"/><path d="M21.984 7.734a.42.42 0 0 0 .346-.665c-1.55-2.204-6.684-8.21-12.969-1.834l-.061.066c-1.718 1.528-3.26 3.76-4.173 6.336-1.941 5.477-.647 10.123 2.658 11.294 3.305 1.172 8.42-2.123 10.362-7.6.725-2.043.945-4.06.77-5.849-.08-.818.483-1.56 1.301-1.647.66-.07 1.258-.11 1.766-.101Z" style="fill:#fab413"/><path d="M6.532 29.874c-.297-.103-.517.274-.283.483 2.236 1.995 7.782 5.884 14.261 2.568 7.119-3.643 7.38-11.682 6.402-15.857-.072-.308-.52-.284-.562.03-.392 2.92-1.934 9.49-7.948 12.181-5.194 2.324-9.717 1.342-11.87.595Z" style="fill:#ca7918"/><path d="M7.257 10.709s.697-3.93 4.722-6.338 4.468 1.807 2.313 2.979-4.257.087-7.035 3.359Z" style="fill:#fcd884"/></symbol><symbol xml:space="preserve" style="enable-background:new 0 0 30.3 36.5" viewBox="0 0 30.3 36.5" id="ico-goldenEgg-callout" xmlns="http://www.w3.org/2000/svg"><path d="M15.1 36.4c9.2.2 16.6-7.4 14.9-17.8C28.5 9.4 22 0 15.1 0S1.7 9.4.2 18.6c-1.7 10.5 5.7 18.1 14.9 17.8z" style="fill:#f7941d"/><path d="M22 7.7c.2 0 .4-.2.4-.4 0-.1 0-.2-.1-.2-1.5-2.2-6.7-8.2-13-1.8C7.6 6.8 6 9.1 5.1 11.6c-1.9 5.5-.6 10.1 2.7 11.3 3.3 1.2 8.4-2.1 10.4-7.6.7-2 .9-4.1.8-5.8-.1-.8.5-1.6 1.3-1.6.6-.1 1.2-.2 1.7-.2z" style="fill:#fab413"/><path d="M6.5 29.9c-.3-.1-.5.3-.3.5 2.2 2 7.8 5.9 14.3 2.6 7.1-3.6 7.4-11.7 6.4-15.9-.1-.3-.5-.3-.6 0-.4 2.9-1.9 9.5-7.9 12.2-5.2 2.3-9.7 1.3-11.9.6z" style="fill:#ca7918"/><path d="M7.3 10.7S8 6.8 12 4.4s4.5 1.8 2.3 3-4.3 0-7 3.3z" style="fill:#fcd884"/><path d="M13 21c-.5 0-.8-.1-.9-.1-.1-.1-.1-.4-.2-.9 0-.1-.1-.2-.1-.3v-1.1c0-.2-.1-.8-.3-1.8v-.5c0-.1-.1-.3-.1-.5V15c0-.4-.1-.6-.1-.7v-.4c0-.2 0-.5-.1-.9 0-.1 0-.3-.1-.6-.1-.6-.2-1.2-.2-1.6 0-.3-.1-.7-.2-1.1v-.1s.1 0 .1-.1c.3-.1.7-.2 1.2-.2h.7c.3 0 .5 0 .7-.1.1 0 .2 0 .4-.1h4.6c.1.1.2.1.3.2.3.1.5.2.7.4.1.1.1.2.1.4 0 .8-.1 1.4-.3 2l-.7 4v.2c-.1.1-.1.2-.1.4l-.1.4v.2c0 .4-.1.9-.2 1.6-.1.5-.3 1.2-.4 2 0 0 0 .1-.1.1s-.2.1-.3.1c-.3.1-1 .1-2.1.1H14c-.4-.1-.7-.1-1-.2zm2.7 7h-1.9c-.7 0-1.2 0-1.6-.1-.2 0-.3 0-.3-.1l-.1-2.4v-1.1c0-.1 0-.2.1-.3v-.5c0-.1 0-.2.1-.3.1-.1.2-.2.4-.2H16c.6 0 1.1 0 1.5.1.5.1.8.2.8.5V25c0 .1 0 .6.1 1.3v1c0 .3-.1.4-.1.6-.2.1-.3.1-.5.2h-.5c-.7 0-1.2 0-1.6-.1z" style="fill:#fff"/></symbol><symbol viewBox="0 0 58 58" id="ico-grenade" xmlns="http://www.w3.org/2000/svg"><path d="m41.24 26.11 1.95-2a1.91 1.91 0 0 0 0-2.71l-3.29-3.23.21-.21a1 1 0 0 0 0-1.45l-3.33-3.32a1 1 0 0 0-1.46 0l-1.92 1.93a4.57 4.57 0 1 0-5.28 7.31C23.8 23 19 25.21 16.69 27.57A10.18 10.18 0 0 0 17.07 42a10.18 10.18 0 0 0 14.4.3c3-3 6-9.87 5.32-14.68l.14.14a1.89 1.89 0 0 0 1.51.55L40 29.92a1.52 1.52 0 0 1 .29 1.65l-5.21 12.12 1.29.5a.88.88 0 0 0 1.05-.45l6.51-12.28a1.54 1.54 0 0 0-.09-1.59Zm-14.3-7.75a3.21 3.21 0 0 1 6.25-1.05l-2 2a1.91 1.91 0 0 0-.37 2.18 2.93 2.93 0 0 1-.67.07 3.21 3.21 0 0 1-3.21-3.2Z"/></symbol><symbol viewBox="0 0 58 58" id="ico-hat" xmlns="http://www.w3.org/2000/svg"><path d="M45.62 33a17.85 17.85 0 0 0-4.76-.7s-1.71-15.38-6-15.38c-2 0-4.32 2-5.9 2s-3.93-2-5.9-2c-4.25 0-6 15.38-6 15.38a17.85 17.85 0 0 0-4.76.7c-1.94.69-2.6 2.61-1.21 3.91 2.07 1.93 7.81 4 17.83 4.13 10-.16 15.76-2.2 17.83-4.13 1.47-1.28.81-3.2-1.13-3.91Z" style="fill:#fff"/></symbol><symbol viewBox="0 0 33.956 34.476" id="ico-map-size-large" xmlns="http://www.w3.org/2000/svg"><path d="M33.868 33.088 30.273 25.1a1.613 1.613 0 0 0-1.47-.95h-5.872a82.739 82.739 0 0 1-4.097 4.925l-1.842 2.033-1.841-2.033a82.739 82.739 0 0 1-4.098-4.926h-5.9a1.61 1.61 0 0 0-1.47.951L.088 33.088a.984.984 0 0 0 .898 1.388H32.97a.984.984 0 0 0 .898-1.388Z" class="akcls-1"/><path d="M16.992 27.408s1.185-1.308 2.75-3.258c3.057-3.806 7.569-10.062 7.569-13.831C27.31 4.619 22.69 0 16.992 0S6.673 4.62 6.673 10.319c0 3.769 4.512 10.025 7.569 13.831a79.831 79.831 0 0 0 2.75 3.258Zm-2.836-12.124V8.496c0-.347.104-.625.312-.833.208-.207.49-.31.846-.31.364 0 .652.103.864.31.212.208.317.486.317.833v5.988h2.924c.737 0 1.107.318 1.107.954 0 .322-.091.558-.274.712-.183.152-.46.228-.833.228h-4.182c-.347 0-.615-.093-.8-.28-.187-.186-.28-.457-.28-.814Z" class="akcls-1"/></symbol><symbol viewBox="0 0 33.956 34.476" id="ico-map-size-med" xmlns="http://www.w3.org/2000/svg"><path d="M30.273 25.1a1.613 1.613 0 0 0-1.47-.95h-5.872a82.739 82.739 0 0 1-4.097 4.926l-1.842 2.033-1.841-2.033a82.739 82.739 0 0 1-4.098-4.926h-5.9a1.61 1.61 0 0 0-1.47.951L.088 33.088a.984.984 0 0 0 .898 1.388H32.97a.984.984 0 0 0 .898-1.388L30.273 25.1Z" class="alcls-1"/><path d="M16.992 27.408s1.185-1.308 2.75-3.258c3.057-3.806 7.569-10.062 7.569-13.831C27.31 4.619 22.69 0 16.992 0S6.673 4.62 6.673 10.319c0 3.769 4.512 10.025 7.569 13.831a79.831 79.831 0 0 0 2.75 3.258ZM14.11 16.122c-.199.2-.457.298-.776.298-.308 0-.562-.097-.762-.29s-.297-.46-.297-.796v-7.21c0-.353.107-.64.324-.861s.492-.331.828-.331c.24 0 .455.068.65.205s.358.334.49.59l2.438 4.585 2.426-4.585c.273-.53.644-.795 1.113-.795.336 0 .61.11.821.33s.319.509.319.862v7.21c0 .336-.098.6-.292.795s-.45.291-.769.291c-.31 0-.562-.097-.761-.29s-.298-.46-.298-.796v-3.896l-1.511 2.783c-.15.282-.31.483-.478.602s-.367.18-.596.18-.429-.06-.596-.18c-.168-.119-.327-.32-.478-.602l-1.497-2.704v3.817c0 .326-.1.59-.298.788Z" class="alcls-1"/></symbol><symbol viewBox="0 0 33.956 34.476" id="ico-map-size-small" xmlns="http://www.w3.org/2000/svg"><path d="M30.273 25.1a1.613 1.613 0 0 0-1.47-.95h-5.872a82.739 82.739 0 0 1-4.097 4.926l-1.842 2.033-1.841-2.033a82.739 82.739 0 0 1-4.098-4.926h-5.9a1.61 1.61 0 0 0-1.47.951L.088 33.088a.984.984 0 0 0 .898 1.388H32.97a.984.984 0 0 0 .898-1.388L30.273 25.1Z" class="amcls-1"/><path d="M16.992 27.408s1.185-1.308 2.75-3.258c3.057-3.806 7.569-10.062 7.569-13.831C27.31 4.619 22.69 0 16.992 0S6.673 4.62 6.673 10.319c0 3.769 4.512 10.025 7.569 13.831a79.831 79.831 0 0 0 2.75 3.258ZM13.48 15.585c-.15-.114-.26-.233-.326-.357s-.098-.282-.098-.477c0-.265.079-.495.238-.69s.345-.291.557-.291c.114 0 .225.017.33.053.107.036.24.102.398.199.363.203.723.35 1.08.437.359.088.763.133 1.213.133.522 0 .916-.077 1.187-.232a.743.743 0 0 0 .403-.683c0-.203-.125-.378-.377-.523s-.731-.29-1.438-.431c-.874-.186-1.56-.406-2.054-.662-.495-.257-.842-.559-1.041-.908-.198-.349-.298-.77-.298-1.266 0-.565.168-1.078.504-1.537.336-.46.797-.82 1.385-1.08s1.248-.392 1.981-.392c.645 0 1.219.071 1.723.213.504.14.963.362 1.378.662.159.115.272.237.338.364.066.13.1.286.1.471 0 .265-.078.495-.232.69-.155.194-.338.29-.55.29-.115 0-.222-.014-.318-.045a2.16 2.16 0 0 1-.411-.206 10.59 10.59 0 0 0-.378-.205c-.207-.11-.45-.199-.729-.265s-.581-.1-.908-.1c-.45 0-.813.086-1.086.26-.274.171-.411.399-.411.68 0 .169.048.306.145.412.098.106.279.21.544.311.265.102.658.205 1.179.312.849.185 1.516.41 2.002.669.485.26.83.563 1.033.908s.305.751.305 1.219a2.54 2.54 0 0 1-.49 1.544c-.328.446-.785.79-1.372 1.034-.587.243-1.27.364-2.047.364a7.943 7.943 0 0 1-1.968-.231c-.606-.156-1.103-.37-1.491-.644Z" class="amcls-1"/></symbol><symbol viewBox="0 0 58 58" style="enable-background:new 0 0 58 58" xml:space="preserve" id="ico-melee" xmlns="http://www.w3.org/2000/svg"><path d="M12.4 46.3c.7.6 1.7.9 2.6.6.6-.2 1.2-.6 1.6-1.1L25 36c.2-.3.2-.6 0-.9v-.2l2.1-2.1h.1c3.1-.5 12.2-2.2 16.7-6.2 1.6-1.4 2.6-3.3 3-5.4.3-2.1 0-4.2-1.1-6.1-.6-1.2-1.6-2.2-2.8-2.8-1.8-1-4-1.4-6.1-1.1-2.1.3-4 1.4-5.4 3-4.1 4.6-5.8 13.6-6.2 16.7v.1l-2.2 2c-.3-.3-.7-.3-1 0l-9.8 8.4c-.5.4-.9 1-1.1 1.6-.3.9-.1 2 .7 2.7l.5.6zm20.4-31c1.4-1.6 3.4-2.6 5.6-2.6.5 0 .9 0 1.4.1-2.2.8-4.1 2.1-5.7 3.8-2.3 2.4-4.3 5.7-5.8 8.6.9-3.5 2.4-7.5 4.5-9.9zm2.6 2.6c2.3-2.5 4.8-3.6 6.6-3.6h.5L28.9 27.9c1.5-3.1 3.9-7.3 6.5-10zm8.3-2.4c.3 1.8-.8 4.5-3.6 7.1-2.8 2.6-6.9 4.9-10 6.5l13.6-13.6zm-1 9.7c-2.4 2.2-6.4 3.6-9.9 4.6 2.9-1.5 6.2-3.6 8.6-5.8 1.7-1.5 3-3.5 3.8-5.7.2 1.3.1 2.6-.3 3.8-.4 1.1-1.2 2.2-2.2 3.1z" style="fill:#fff"/></symbol><symbol viewBox="0 0 37.146 24.123" id="ico-nav-equipment" xmlns="http://www.w3.org/2000/svg"><path d="M35.197 16.086c-1.938-.688-4.76-.704-4.76-.704S28.723 0 24.475 0c-1.973 0-4.32 2.004-5.902 2.004S14.643 0 12.67 0C8.423 0 6.709 15.382 6.709 15.382s-2.822.016-4.76.704c-1.94.688-2.6 2.608-1.207 3.906 2.066 1.926 7.813 3.965 17.83 4.13 10.019-.165 15.766-2.204 17.832-4.13 1.392-1.298.732-3.218-1.207-3.906Z"/></symbol><symbol viewBox="0 0 43.927 30.364" id="ico-nav-friends" xmlns="http://www.w3.org/2000/svg"><path d="m38.849 15.363-.008-.002a6.921 6.921 0 0 0-.34-.086l-.034-.008a6.956 6.956 0 0 0-.32-.063l-.056-.01a6.98 6.98 0 0 0-.308-.043l-.069-.008a7.047 7.047 0 0 0-.312-.026l-.067-.005a7.063 7.063 0 0 0-.378-.011h-.08a6.26 6.26 0 0 0-4.893-11.438 10.81 10.81 0 0 1 .933 4.402c0 1.95-.527 3.832-1.48 5.468 3.902 1.977 6.478 6.035 6.478 10.524v2.91h6.012v-4.896a6.972 6.972 0 0 0-5.078-6.708Zm-33.77 0 .007-.002c.112-.032.226-.06.34-.086l.035-.008c.105-.024.212-.044.32-.063l.055-.01a6.98 6.98 0 0 1 .308-.043l.07-.008c.103-.012.207-.02.311-.026l.067-.005c.125-.007.251-.011.379-.011h.08a6.26 6.26 0 0 1 4.893-11.438 10.81 10.81 0 0 0-.934 4.402c0 1.95.527 3.832 1.48 5.468-3.902 1.977-6.478 6.035-6.478 10.524v2.91H0v-4.896a6.972 6.972 0 0 1 5.079-6.708Z" class="apcls-1"/><path d="m28.483 15.415-.01-.003a8.895 8.895 0 0 0-.438-.11l-.044-.01a8.93 8.93 0 0 0-.412-.081c-.024-.005-.048-.01-.071-.013a8.975 8.975 0 0 0-.397-.055l-.09-.012a9.02 9.02 0 0 0-.401-.033l-.086-.007a9.11 9.11 0 0 0-.488-.013h-.103a8.064 8.064 0 1 0-7.96 0h-.102a8.98 8.98 0 0 0-8.98 8.98v6.306h26.125v-6.307c0-4.114-2.768-7.58-6.543-8.642Z" class="apcls-1"/></symbol><symbol viewBox="0 0 32.36 31.597" id="ico-nav-home" xmlns="http://www.w3.org/2000/svg"><path d="M15.468.295.297 15.465c-.634.635-.185 1.72.712 1.72H4.57c.556 0 1.006.45 1.006 1.006V30.59c0 .556.451 1.007 1.007 1.007h4.91c.556 0 1.006-.451 1.006-1.007v-8.174c0-.556.451-1.007 1.007-1.007h5.345c.556 0 1.007.451 1.007 1.007v8.174c0 .556.45 1.007 1.007 1.007h4.91c.555 0 1.006-.451 1.006-1.007v-12.4c0-.556.45-1.006 1.007-1.006h3.562c.897 0 1.346-1.085.712-1.72L16.892.296a1.007 1.007 0 0 0-1.424 0Z"/></symbol><symbol viewBox="0 0 26.805 31.155" id="ico-nav-profile" xmlns="http://www.w3.org/2000/svg"><path d="m20.092 15.817-.01-.003a9.136 9.136 0 0 0-.45-.114l-.045-.01a9.18 9.18 0 0 0-.423-.083c-.025-.004-.049-.01-.073-.013a9.204 9.204 0 0 0-.407-.057l-.092-.011a9.276 9.276 0 0 0-.413-.035l-.087-.007a9.352 9.352 0 0 0-.501-.014h-.105a8.275 8.275 0 1 0-8.167 0h-.105A9.214 9.214 0 0 0 0 24.684v6.47h26.805v-6.47c0-4.221-2.84-7.777-6.713-8.867Z"/></symbol><symbol viewBox="0 0 33.411 31.155" id="ico-nav-shop" xmlns="http://www.w3.org/2000/svg"><path d="M33.124 5.345a1.35 1.35 0 0 0-1.055-.528H7.877l-.95-3.797A1.322 1.322 0 0 0 5.627 0h-4.29a1.336 1.336 0 1 0 0 2.672h3.27l5.415 22.083a1.32 1.32 0 0 0 1.3 1.02H26.76a1.336 1.336 0 1 0 0-2.673H12.342l-.773-3.13h17.3c.598 0 1.126-.421 1.301-.984l3.2-12.518a1.3 1.3 0 0 0-.246-1.125Z"/><circle cx="13.819" cy="29.01" r="2.145"/><circle cx="24.896" cy="29.01" r="2.145"/></symbol><symbol viewBox="0 0 58 58" id="ico-primary" xmlns="http://www.w3.org/2000/svg"><path d="m48.31 10.74-1.93 1.62a12.45 12.45 0 0 1-3.79-3.42l-.88.75A16.38 16.38 0 0 1 44.45 14l-.39.33L41.32 13l-1.52 1.21-.74-.44-5.23 4.3.12 1-.22.18-1.62-.48-2.39 2.18-.59-.37-.53.54 1.16 1.41L17 33l.2 3-1.2 2-1.39-.08-6.39 5.55L13 49l6.67-10.6.58-.25 3.94 10.91L29.08 45l-2.9-6.32 3.23-3-1.35-2.25c1.25 1.23 8.23 7.67 17.55 7.08l.57-6.88s-8 .58-13.25-5.32l.16-.57 10.15-8.92-.09-.82 6.63-5.72ZM25.62 37.42l-.71-1.56 1.64-.81-.2-.59-2 .14 2.11-1.42 1.34 2.21ZM12.9 20.87a5.82 5.82 0 0 0 1.75 1.31 5.64 5.64 0 0 0 2.15.59h.45a5.82 5.82 0 0 0 2.18-.41 6 6 0 0 0 1.86-1.16 5.85 5.85 0 0 0 1.9-3.92v-.45a6 6 0 0 0-1.58-4 6 6 0 0 0-1.75-1.31 5.87 5.87 0 0 0-2.15-.58h-.45a6 6 0 0 0-2.18.41 6.14 6.14 0 0 0-1.86 1.16 6 6 0 0 0-1.32 1.76 5.78 5.78 0 0 0-.59 2.15v.45a6 6 0 0 0 .42 2.13 6.22 6.22 0 0 0 1.17 1.87Zm2.24-7.79a.76.76 0 0 1 .63-.26h1.55a.83.83 0 0 1 .93.94v6.41a1 1 0 0 1-.26.74.87.87 0 0 1-.67.26 1 1 0 0 1-.7-.26 1 1 0 0 1-.26-.74v-5.61h-.59a.79.79 0 0 1-.63-.26.85.85 0 0 1-.23-.61.87.87 0 0 1 .23-.61Z" style="fill:#fff"/></symbol><symbol viewBox="0 0 58 58" id="ico-secondary" xmlns="http://www.w3.org/2000/svg"><path d="m43.75 16.19.79-.58-2.62-3.29-1.87 1.49-1.52-.81L37 14.12l.53 1.72L29 22.65l-1.39.08-6.38 5.1-.78-.65-1.35 1 .47 1-.7.56a.72.72 0 0 0-.17.93l3.16 5.27 2.22.33 4.33 9.48 2.64 1 4.56-4.37.27-2.72-2.37-5.37 5-4.59-2.25-3.3 2.8-2.4-.34-.89 5.61-4.58-.33-.48.63-.48Zm-7.31 13.27-3.57 3.31-.41-.93 1.67-1.66-2.52-1.47 2.36-2 .57.31Zm-14.38-7.83a5.87 5.87 0 0 0 1.31-1.76 5.68 5.68 0 0 0 .63-2.15v-.45a6 6 0 0 0-3.33-5.35 5.87 5.87 0 0 0-2.15-.58h-.45a6 6 0 0 0-4 1.58 6.16 6.16 0 0 0-1.32 1.75 5.68 5.68 0 0 0-.58 2.15v.45a5.91 5.91 0 0 0 1.58 4 6.16 6.16 0 0 0 1.75 1.32 5.83 5.83 0 0 0 2.15.59H18a6 6 0 0 0 2.18-.41 6.15 6.15 0 0 0 1.88-1.14Zm-5.81-.16a1.45 1.45 0 0 1-.84-.22.81.81 0 0 1-.31-.6 1.4 1.4 0 0 1 .44-.93l.77-.81.1-.1.71-.79.35-.39a3.28 3.28 0 0 0 .23-.28 9.49 9.49 0 0 0 .72-1.09 1.91 1.91 0 0 0 .21-.72.65.65 0 0 0-.2-.53.69.69 0 0 0-.54-.2.61.61 0 0 0-.53.22 2.34 2.34 0 0 0-.34.64 3 3 0 0 1-.33.65.71.71 0 0 1-.54.23.91.91 0 0 1-.65-.23 1 1 0 0 1-.28-.62 2.55 2.55 0 0 1 .38-1.35 2.9 2.9 0 0 1 1-1A3 3 0 0 1 18 13a2.54 2.54 0 0 1 1 .19 2.17 2.17 0 0 1 .85.51 2.39 2.39 0 0 1 .58.8 2.33 2.33 0 0 1 .21 1 2.75 2.75 0 0 1-.26 1.19 4.21 4.21 0 0 1-.58.94 11.56 11.56 0 0 1-.93 1.05 11.15 11.15 0 0 0-.87 1h2a.86.86 0 0 1 .64.25 1.11 1.11 0 0 1 .23.62.79.79 0 0 1-.25.61.78.78 0 0 1-.65.27Z" style="fill:#fff"/></symbol><symbol viewBox="0 0 33.956 34.086" id="ico-settings" xmlns="http://www.w3.org/2000/svg"><path d="m32.988 14.471-2.331-.412c-.48-.068-.892-.48-1.03-.891a17.065 17.065 0 0 0-.754-1.989c-.205-.411-.205-.96.138-1.372l1.44-1.92a1.245 1.245 0 0 0-.137-1.577l-1.098-1.166-1.097-1.166a1.245 1.245 0 0 0-1.577-.137l-1.92 1.371c-.412.275-.96.343-1.372.069a9.532 9.532 0 0 0-1.92-.823 1.254 1.254 0 0 1-.892-1.029l-.343-2.4C20.026.41 19.478 0 18.929 0h-3.155c-.617 0-1.097.411-1.234 1.029l-.412 2.332c-.069.48-.48.891-.96 1.028-.686.206-1.372.48-2.058.823-.411.206-.96.206-1.371-.137l-1.92-1.44a1.245 1.245 0 0 0-1.578.137L5.075 4.869 3.91 5.967a1.245 1.245 0 0 0-.137 1.577l1.372 1.989c.274.412.274.96.068 1.372a10.125 10.125 0 0 0-.823 1.989c-.137.48-.548.823-1.028.891l-2.332.343c-.55.069-1.03.617-1.03 1.166v3.155c0 .617.412 1.097 1.029 1.234l2.332.412c.48.068.891.411 1.028.891.206.686.48 1.372.755 1.99.205.41.205.96-.137 1.371l-1.44 1.92a1.245 1.245 0 0 0 .136 1.578l1.098 1.166 1.097 1.166c.412.411 1.097.48 1.578.137l1.988-1.372c.412-.274.96-.274 1.372-.069.617.343 1.303.618 1.989.823.48.138.823.55.892 1.03l.343 2.331c.068.617.617 1.029 1.165 1.029h3.155c.617 0 1.098-.412 1.235-1.029l.411-2.332c.069-.48.48-.891.892-1.029.617-.205 1.303-.48 1.852-.754.411-.206.96-.206 1.371.137l1.92 1.44c.48.343 1.166.275 1.578-.137l1.166-1.097 1.166-1.097c.411-.412.48-1.098.137-1.578l-1.372-1.92c-.274-.412-.274-.96-.068-1.372a9.533 9.533 0 0 0 .823-1.92c.137-.48.548-.823 1.028-.892l2.4-.343c.618-.068 1.03-.617 1.03-1.166v-3.154c.068-.755-.343-1.303-.96-1.372ZM16.94 23.935c-3.84 0-6.995-3.154-6.995-6.995s3.154-6.995 6.995-6.995 6.995 3.154 6.995 6.995-3.154 6.995-6.995 6.995Z"/></symbol><symbol viewBox="0 0 58 58" id="ico-specialItem" xmlns="http://www.w3.org/2000/svg"><path d="M42.05 25.32h-8.88a.74.74 0 0 1-.71-.52l-2.75-8.44a.75.75 0 0 0-1.42 0l-2.75 8.44a.74.74 0 0 1-.71.52H16a.75.75 0 0 0-.44 1.35l7.19 5.22a.76.76 0 0 1 .27.84l-2.75 8.45a.75.75 0 0 0 1.16.83l7.18-5.22a.75.75 0 0 1 .88 0L36.62 42a.75.75 0 0 0 1.16-.83L35 32.73a.76.76 0 0 1 .27-.84l7.19-5.22a.75.75 0 0 0-.41-1.35Z" style="fill:#fff"/></symbol><symbol id="ico-stamp" viewBox="0 0 58 58" xmlns="http://www.w3.org/2000/svg"><defs><style>.axcls-1{fill:#fff}</style></defs><circle class="axcls-1" cx="26.49" cy="29.42" r="1.41"/><circle class="axcls-1" cx="31.86" cy="29.42" r="1.41"/><path class="axcls-1" d="M32.65 32.55a.91.91 0 0 0-1.11.64 2.4 2.4 0 0 1-4.64 0 .91.91 0 1 0-1.75.47 4.21 4.21 0 0 0 8.14 0 .91.91 0 0 0-.64-1.11Z"/><path class="axcls-1" d="M29 11c-8.2 0-14.86 13-14.86 21.22a14.86 14.86 0 0 0 29.72 0C43.86 24 37.2 11 29 11Zm.18 29.16a8.53 8.53 0 1 1 8.52-8.53 8.53 8.53 0 0 1-8.52 8.49Z"/></symbol><symbol viewBox="0 0 29.297 30.465" id="ico-star" xmlns="http://www.w3.org/2000/svg"><path d="m11.527.247 6.535 6.356a.87.87 0 0 0 .777.23l8.94-1.782a.87.87 0 0 1 .951 1.238l-4.024 8.18a.87.87 0 0 0 .021.81l4.457 7.952a.87.87 0 0 1-.884 1.287l-9.022-1.3a.87.87 0 0 0-.764.27l-6.186 6.696a.87.87 0 0 1-1.497-.442L9.28 20.759a.87.87 0 0 0-.493-.642l-8.28-3.815a.87.87 0 0 1-.042-1.56l8.064-4.252a.87.87 0 0 0 .459-.668L10.056.77a.87.87 0 0 1 1.47-.522Z"/></symbol><symbol class="azvip-svg-icon-wrap" viewBox="0 0 58 58" style="enable-background:new 0 0 58 58" xml:space="preserve" id="ico-vip" xmlns="http://www.w3.org/2000/svg"><path class="azvip-svg-icon azvip-svg-icon-wings" d="M28.1 42.7s-.8-.1-1.5-.2c-.4-.1-.8-.1-1.1-.1-.3-.1-.5-.1-.5-.1-.2-2.9-1.2-4.3-2-5.1-.5-.4-1.8-1-1.8-1 0 .9-.1 2 .2 3.1.3 1 1 2.1 2.4 2.7-.1.1-4.2-1.4-4.1-1.6.3-1.4.3-2.5.1-3.4-.1-.9-.3-1.5-.6-2s-1.3-1.4-1.3-1.4c-.3.8-.7 1.8-.8 2.9-.1.6.1 1.1.3 1.7.2.5.6 1.2 1.2 1.6 0 0-1-.6-1.9-1.3-.5-.3-.9-.7-1.2-1L15 37c1.3-2.5 1.8-4.2 1-6.4-.2-.3-.3-.4-.3-.4-1.1 1.2-3 3.2-1.5 6 0 0-.7-.9-1.3-1.8-.3-.4-.5-1-.7-1.3l-.3-.6c2.1-1.9 3-3.2 3.1-4.3.2-1.1-.1-2.1-.1-2.1-1.5.9-4 2.2-3.5 5.3l-.2-.6c-.1-.4-.2-.9-.4-1.5-.2-1.1-.3-2.2-.3-2.2 2.7-1 3.8-2.3 4.3-3.4s.4-2 .4-2c-1.7.5-4.4 1.1-4.8 4.1 0 0 0-1.1.1-2.2s.3-2.2.3-2.2c2.9-.3 4.3-1.2 5-1.9.4-.4.9-1.5.9-1.5-.9-.2-1.9-.3-3-.1-1.1.2-2.1.9-2.7 2.3 0 0 0-.2.1-.5s.2-.6.3-1c.3-.7.5-1.4.5-1.4.6-.2 1.4-.6 2.1-1.3.7-.7 1-1.8 1.7-3.9-2.4-.2-3.9 1.2-4.4 2.3-.6 1.2-.3 2.2-.1 2.7 0 0-.3.7-.6 1.5-.1.4-.2.8-.3 1.1-.1.3-.1.5-.1.5.2-1.6-.1-2.9-.5-4.1-.4-1.2-.9-2.3-1.1-3.4 0 0-.3.3-.7.9-.3.6-.7 1.4-.9 2.3-.3 1.8.4 4.1 3 5.3 0 0-.2 1.2-.3 2.3-.1 1.2 0 2.4 0 2.4-.5-3.2-2.6-4.7-3.9-6.4 0 0-.6 1.5-.4 3.2.2 1.8 1.4 3.8 4.3 4.3 0 0 .1 1.2.3 2.3.2 1.2.6 2.3.6 2.3-1.4-2.9-3.7-3.9-5.5-5.2 0 0-.2 1.5.5 3.2.7 1.6 2.4 3.5 5.4 3 0 0 .1.3.3.7.2.4.4 1 .8 1.4.7 1 1.4 1.9 1.4 1.9-2.3-2.2-4.9-2.3-7-3.1 0 0 .1.4.4 1 .3.6.7 1.4 1.3 2.1 1.4 1.3 3.6 2.3 6.2.8l.5.5c.3.3.8.7 1.3 1.1.5.4 1 .7 1.4 1 .3.2.6.3.6.4-.8-.2-1.4-.5-2.1-.6-.7-.1-1.4 0-1.9-.1-1.2 0-2.4.3-3.5.3 0 0 .2.3.7.8.5.4 1.1 1.1 1.9 1.4.9.4 1.9.6 2.9.5 1.1-.2 2.1-.7 3.1-1.8-.1.1 3.5 1.5 4.3 1.6h-2.1c-.7.1-1.3.3-1.9.5-1.2.4-2.2 1-3.2 1.4 0 0 .3.2.8.5s1.3.6 2.2.7c1.8.3 4-.3 5.3-2.9 0 0 .2 0 .5.1.3 0 .7.1 1.1.1l1.6.2c.2 0 .4-.2.4-.4.2.2 0 0-.2 0zm1.8 0s.8-.1 1.5-.2c.4-.1.8-.1 1.1-.1.3-.1.5-.1.5-.1.2-2.9 1.2-4.3 2-5.1.5-.4 1.8-1 1.8-1 0 .9.1 2-.2 3.1-.3 1-1 2.1-2.4 2.7 0 .1 4.2-1.4 4.1-1.6-.3-1.4-.3-2.5-.1-3.4.1-.9.3-1.5.6-2s1.3-1.4 1.3-1.4c.3.8.7 1.8.7 2.9.1.6-.1 1.1-.3 1.7-.2.5-.6 1.2-1.2 1.6 0 0 1-.6 1.9-1.3.5-.3.9-.7 1.2-1l.5-.5c-1.3-2.5-1.8-4.2-1-6.4.2-.3.3-.4.3-.4 1.1 1.2 3 3.2 1.5 6 0 0 .7-.9 1.3-1.8.3-.4.5-1 .7-1.3l.3-.6c-2.1-1.9-3-3.2-3.2-4.3-.2-1.1.1-2.1.1-2.1 1.5.9 4 2.2 3.5 5.3l.2-.6c.1-.4.2-.9.4-1.5.2-1.1.3-2.2.3-2.2-2.7-1-3.8-2.3-4.3-3.4s-.5-2-.5-2c1.7.5 4.4 1.1 4.8 4.1 0 0 0-1.1-.1-2.2s-.3-2.2-.3-2.2c-2.9-.3-4.2-1.2-5-1.9-.4-.4-.9-1.5-.9-1.5.9-.2 1.9-.3 3-.1 1 .2 2.1.9 2.7 2.3 0 0 0-.2-.1-.5s-.2-.6-.3-1c-.3-.7-.5-1.4-.5-1.4-.6-.2-1.4-.6-2.1-1.3-.7-.7-1-1.8-1.7-3.9 2.4-.2 3.9 1.2 4.4 2.3.6 1.2.3 2.2.1 2.7 0 0 .3.7.6 1.5.1.4.2.8.3 1.1.1.3.1.5.1.5-.2-1.6.1-2.9.5-4.1.4-1.2.9-2.3 1.1-3.4 0 0 .3.3.7.9.3.6.7 1.4.9 2.3.4 1.9-.3 4.2-2.9 5.4 0 0 .2 1.2.3 2.3.1 1.2 0 2.4 0 2.4.5-3.2 2.6-4.7 3.9-6.4 0 0 .6 1.5.4 3.2-.2 1.8-1.4 3.8-4.3 4.3 0 0-.1 1.2-.3 2.3-.2 1.2-.6 2.3-.6 2.3 1.4-2.9 3.7-3.9 5.5-5.2 0 0 .2 1.5-.5 3.2-.7 1.6-2.4 3.5-5.4 3 0 0-.1.3-.3.7-.2.4-.4 1-.8 1.4-.7 1-1.4 1.9-1.4 1.9 2.3-2.2 4.9-2.3 7-3.1 0 0-.1.4-.4 1-.3.6-.7 1.4-1.3 2.1-1.4 1.3-3.6 2.3-6.2.8l-.5.5c-.3.3-.8.7-1.3 1.1-.5.4-1 .7-1.4 1-.3.2-.6.3-.6.4.8-.2 1.4-.5 2.1-.6.7-.1 1.4 0 2-.1 1.2 0 2.4.3 3.5.3 0 0-.2.3-.7.8-.5.4-1.1 1.1-1.9 1.4-.9.4-1.9.6-2.9.5-1.1-.2-2.1-.7-3.1-1.8.1.1-3.5 1.5-4.3 1.6h2.1c.7.1 1.3.3 1.9.5 1.2.4 2.2 1 3.2 1.4 0 0-.3.2-.8.5s-1.3.6-2.2.7c-1.8.3-4-.3-5.3-2.9 0 0-.2 0-.5.1-.3 0-.7.1-1.1.1l-1.6.2c-.2 0-.4-.2-.4-.4-.1.1.1-.1.3-.1z"/><path class="azvip-svg-icon azvip-svg-icon-emblem" d="M29 12.7c-.1 0-.2 0-.3.1-1.9 1-7.1 3.2-9.4 4.1-.7.3-.8.4-.8.6-.1 2.5-.3 8.6 1.2 12.3 2 4.7 7.5 7 9.1 7.6h.4c1.6-.6 7.1-2.9 9-7.6 1.5-3.7 1.4-9.8 1.2-12.3 0-.2-.1-.4-.8-.6-2.2-.9-7.5-3-9.3-4.1-.1-.1-.2-.1-.3-.1z"/></symbol><symbol viewBox="0 0 106 106" xml:space="preserve" id="ico-weapon-crackshot" xmlns="http://www.w3.org/2000/svg"><path class="bast0" d="m97.3 9.6-3.1-3.1-3.6 2-36.2 31.7h-.8l-1.4-1.6 1.4-1.2 4.2-2 3.7-2.8-4.9-5.6-3.6 2.8-3.8 5.3-.4-.5-1.9 1.6.4.5-9.8 8.3-.5-.6-1.9 1.6.5.6-6.6 3.3-1.8 1.8 5.2 5.7 2.1-1.8 4-5.6 1.5 1.8-.5 1.6-1.1-.8-1.6 1.2.6.9-1.9 1.7 2.9 3.2-3.4 6.2-3.9.4-17 14 2.4 2.1-1.5 1.5-2.3-1.9-4.1 3.9 16.6 13.8 3.3-4.2-2.3-1.9 1.2-1.3 1.5 1.3L41 75.4l6 2.4 4-4-5.7-6-.4-2.8 3.3-4.3 2.3 2.4h6.3l3.7-3.2 1-5.7-3.2-3.6 22-19-3.1-4 16.9-14.1 3.2-3.9zM18.1 83.7l3 2.7-1.4 1.3-3.2-2.7 1.6-1.3zm6.2 7.9-2.4-2 1.3-1.3 2.3 2-1.2 1.3zm17.3-42-1-1.1 9.8-8.2 1.1 1.2-9.9 8.1zm17.3 5.6-.4 3.5-2.5 2.4-4.6.3-1.9-2.1.7-.9 1.3 1.4h3.4l.9-.8-2.8-.7-1.4-1.7.2-.3 4.6-4 2.5 2.9z"/></symbol><symbol viewBox="0 0 106 106" xml:space="preserve" id="ico-weapon-ranger" xmlns="http://www.w3.org/2000/svg"><path class="bbst0" d="m76.8 32.6-.1-2.2 22.5-19.3-3.3-3.3-11.4 9.6-4.7-1.3-7.6 6.5-2.2-1-25.7 21.8-3.6-3.5.9-.7 6.5-3.9 2.7.2 1.2-1.1s-1.8-3.4-6.9-8l-1.2 1.3v2.1l-4.6 5.8-8.3 8-3.9 1.7-13.9 11.1-2.9.9s1 2.1 2.4 3.6c2 2 4 3.2 3.7 3l.8-2.6 13.1-11.7 2.5-3.1.2-.2 3.5 3.8-12.4 10.6 1.7 8.9-19 16.5L17 98.2l16.9-17 3.4 7.5 11.3-6.3-5-9.4 9.4-9.8-2.2-3.8 1.5.8 6.6 5.5 8.7-10.8c-7.7-4.6-9.6-8-9.6-8l5.2-2 13.6-12.3zm1.2-12 1.8.6-4.6 4-1-1.1 3.8-3.5zM17.5 89.1l-3.5-5 2.5-2.2 3.8 4.4-2.8 2.8zm8.1-4.2-4.4-7.3L29 71l3.5 7.3-6.9 6.6zm23.9-22.1-7.3 7.6-2.5-4.6 4.9-2.3-.3-2c-1.5.6-2.5-.1-3.2-.7l4.9-3.7.1.1 3.4 5.6z"/></symbol><symbol viewBox="0 0 106 106" xml:space="preserve" id="ico-weapon-rpegg" xmlns="http://www.w3.org/2000/svg"><path class="bcst0" d="m78.1 48.5-5.1-.1.1-1.7 6.2-5.1 1.4-3.2.9 1.1 19.2-13.1s-.9-3.1-3.7-7l.2-1.8c.2-1.2-.7-2.3-1.9-2.4l-1.9-.2c-1.7-1.7-3.7-3.4-6.2-5L70.9 26.6l1.3 1.5-1.5.5-5.5-2.6-4.9 4.1.8 4.4-4.7 3.8-4-4.6.1-.1 1.4.3 7.4-6.9.6.7 4.2-3.4-5.3-6.3-4.2 3.4.4.6-7.8 6v1.5l-12.4 8.6 5.4 6.2 5.7-6 3.4 4.7-1.6-.4-3.7 2.8 1 1.3-22.3 19-1.4-.5-3.5 2.8.2 1.3-8.8 5.2-2.2-.3-3.9 3.5s3.4 11.5 14.7 18.2l4.4-3.5.3-2.2 6-7.5.6-.5 1.3.4 3.2-2.8-.1-1.4 2.9-2.5 11.1 11.6 7.1-6.2-10-12.6 9.5-8.2 8.3.2.2.3-2.1 2.1 5.1 6.8 1.8-1.7 5.8 9 6.5-6.1-4.2-10.8 5.2-5.7-4.6-6.1zm-1.4 2.6 2.5 3.6-3.2 3.4-2-2.5 2.5-1.9-.6-.7-3.1-.1.1-1.9 3.8.1zM63.2 31.2l2.4-2 1.4.7-.3.1-3.3 2.6-.2-1.4z"/></symbol><symbol viewBox="0 0 106 106" xml:space="preserve" id="ico-weapon-scrambler" xmlns="http://www.w3.org/2000/svg"><path class="bdst0" d="M94.5 15.3c-.3-.6-.9-1.5-1.8-2.6-.9-1-1.6-1.8-2.2-2.3-.5-.4-1.1-.4-1.6-.1l-3.7 3-2.2-.6-3.1 2.2.5 2.2L46 44.6c-.2.2-.4.4-.7.6l-2.6-2.5-1.7 1.5 2.5 3c-5.2 6.5-10 17.3-10 17.3l-3.3.2L11.4 83l11.3 12.9L41 67.7l2.8 1.5.6-.2c7.9-2.4 9.5-7.5 9.5-7.7l.2-.7-3.9-5.1 10.5-6.8L85.1 28c.8-.7.9-1.8.3-2.7l-.7-1 9.4-7.5c.5-.3.7-1 .4-1.5zM44.1 66.1l-1.5-.8 2.5-3.8h3l.9-1-2.1-1.5 1.2-1.4 2.7 3.5c-.5 1.1-2.3 3.5-6.7 5z"/></symbol><symbol viewBox="0 0 106 106" xml:space="preserve" id="ico-weapon-soldier" xmlns="http://www.w3.org/2000/svg"><path class="best0" d="m61.7 51.4.4-1.3 22.4-19.7-.2-1.8 14.6-12.7-3.3-3.3-4.3 3.6C86.7 13.9 83 8.7 83 8.7l-1.9 1.7c3.5 4 5.2 7.5 6.1 9.5l-.9.7-6.1-3-3.4 2.8-1.6-1-11.5 9.5.2 2.2-.5.4-3.6-1.1-5.3 4.8-1.3-.8-1.2 1.2 2.6 3.1-28 23.2.4 6.5-2.7 4.5-3.1-.2L7.1 85l10.5 12.1 14.8-23.3 1.3-.5 8.7 24.1 10.8-9.1-6.4-13.9 7.1-6.7-3-5c2.8 2.7 18.2 16.9 38.8 15.6L91 63.1s-17.8 1.3-29.3-11.7zm24.9-36.3c.2-.2.5-.2.8 0l1.6 1.2c.3.2.3.7 0 1l-.6.5c-.3.2-.7.2-.9-.2l-.9-1.7c-.3-.3-.2-.7 0-.8zM45.5 71.6 44 68.2l4.2-2.1-.5-.9-2.7-.7.9-2 1.4-.3 2.9 4.9-4.7 4.5z"/></symbol><symbol viewBox="0 0 106 106" xml:space="preserve" id="ico-weapon-trihard" xmlns="http://www.w3.org/2000/svg"><path d="m94.3 7.4-2.8.5-1 3-12.1 11.8-1.1-.9-5 4.5 3.8 4.1.6 2.9-2 1.4-5.4-6.5-9-1-12 10.3-.5-.5 2-1.7-3.5-4.3-2.1 2-.7-.7-5.1 4.9.7.7-2 1.8 3.1 3.7 2.4-2 .6.6-6.2 5.2 1.8 1.9 7.9-6.8h1l1.1 1.2.2.7-.3.3.3 2.3-2 1.3-1.2-1.1-28.2 21.2 2.2 2.7-12.1 9.4v4.2l9.4 8.6 1.7-.6 6.2 6.1 5.2-4.6.6-14h2.9l1.5-1.4s2.9 3.2 6.7 6.1c3.8 2.9 10.3 8.1 10.3 8.1L58.3 81v-2.4l-2.7-.6s-8-4.8-11.6-9.1l1.1-1.1-1.1-2.6 7.4-6.5L59 70.5l3.4 1.1 9.5-8-5.7-15.9 1.9-1.4 1.5 1.1 2.3-1.8-1-1.9 1-.8 11.7 10.9 2.7-2.5L77.4 38l4.3-3.3-.7-6.2-.8-1.3.3-1.5L95 15.3l2.8-.5.5-2.9-4-4.5zm-37 31.2-4.3.6.8 2.3-1.8.5-1.1-.3-.9-1.3v-.9l11-9.6 2.3 1.5-.4 2.3-3.2-.1.9 2.2-4.2.4.9 2.4zm11.1 23.6-4.6 4.4-4.6-8 .8-2.4.9-.1-.1-1.6-1.7-2.5 4.3-3.8 5 14z"/></symbol><symbol viewBox="0 0 106 106" xml:space="preserve" id="ico-weapon-whipper" xmlns="http://www.w3.org/2000/svg"><path class="bgst0" d="m98 16.8-2.9-3.4c-.4-.4-1.1-.5-1.5-.1L90.4 16c-.3.3-.5.7-.3 1.1l-6.4 5.6-5.4-5.7-2-1-4-3.9-.7.5-.5 1.1-1.7-2.1-1 .8 1.7 2.1-1.2.2-1.8 1.4.2 1.1-1.1.8-1.1-.4-1.7 1.3.2 1.2-.9.9-1.1-.4-1.7 1.4.2 1.2-1 .8-1.2-.4-1.6 1.3.2 1.3-1.1.8-1.3-.4-1.7 1.4v1.3l-2.8-3.5-1.8 1.5 3.7 4.5-.8 1.2 1.8 8.1L7.8 78l16 16.3H26l18.4-16 10.5-.5 9.5-5.5 5.2-7.1v-7.4l9-6.2 2.1 4.1 5.3-4.2v-6.9l-3.7-6.2 3.7-3.2 3.9 4.2 4.1-3.3-4.7-6.1h-4l.8-2.2 7.6-6.6c.3.1.7 0 1-.2l3.2-2.7c.4-.4.5-1.1.1-1.5zM58.6 66.1l-6.2 4.5-6.6-7.3 10.6-9c1.5 1.9 4.6 4.9 4.6 4.9l-2.4 6.9zm-.8-29.5-.6-2.3 14.4-12.1 3 .5-16.8 13.9zM74.4 50l-4.1-3.3-.8-3.3 5.5-4.6 4.4 6.2-5 5z"/></symbol></svg>		<!-- Ads -->
		<div id="gameAdContainer" class="hideme">
</div>

<div id="shellshockers_titlescreen"></div>
<div id="shellshockers_chicken_nugget_banner"></div>
<div id="shellshockers_respawn_banner-pr2"></div>
<div id="shellshockers_respawn_banner_2-pr"></div>
<div id="shellshockers_respawn_banner"></div>
<div id="shellshockers_respawn_banner-new"></div>
<div id="ShellShockers_LoadingScreen_HouseAds"></div>

<div id="videoAdContainer">
    <div id="preroll"></div>
</div>

<!-- <div class="video_ad_wrapper">
    <video id="asc_video_ad" class="video-js vjs-default-skin" controls preload="auto" width="640" height="360" muted="true" style="display: none;">
        <source src="video/tiny.mp4" type="video/mp4" />
    </video>
</div> -->
		<div id="ss_background"></div>

		<!-- Instantiate the Vue instance -->
		<div id="app" :class="[currentLanguageCode, appClassObj, appClassScreen]"> <!-- vue instance div: all vue-controlled elements MUST be inside this tag -->
<!-- <asc-video-player id="mainVideoPlayer" ref="mainVideoPlayer" adTagUrl="adTagUrl"></asc-video-player> -->
    <div class="firebaseID">firebase ID: {{ firebaseId }}, maskedEmail: {{ maskedEmail }} isAnonymous: {{ isAnonymous }}, isEmailVerified: {{ isEmailVerified }}</div>
    <!-- Canvas -->
	<div ref="gameCanvas" class="canvas-wrapper gameCanvas" v-show="showScreen !== screens.profile">
		<canvas id="canvas" ref="canvas"></canvas>
	</div>
    <!-- Overlays -->
    <light-overlay id="lightOverlay" ref="lightOverlay"></light-overlay>
    <dark-overlay id="darkOverlay" ref="darkOverlay"></dark-overlay>
    <spinner-overlay id="spinnerOverlay" ref="spinnerOverlay" :loc="loc" :hide-ads="hideAds" :ad-unit="displayAd.adUnit.spinner"></spinner-overlay>

    <!-- GDPR -->
    <gdpr id="gdpr" ref="gdpr" :loc="loc"></gdpr>
		<!-- <account-panel id="account_panel" ref="accountPanelHome" :loc="loc" :selected-language-code="currentLanguageCode" :eggs="eggs" :languages="languages" :currentLangOptions="locLanguage" :show-corner-buttons="ui.showCornerButtons" :show-bottom="true" :photo-url="photoUrl" :is-anonymous="isAnonymous" :is-of-age="isOfAge" :show-targeted-ads="showTargetedAds" :current-screen="showScreen" :screens="screens" :isEggStoreSale="isEggStoreSaleItem" :is-subscriber="isSubscriber" @sign-in-clicked="onSignInClicked" @sign-out-clicked="onSignOutClicked" :is-twitch="twitchLinked"></account-panel> -->
	<account-panel id="account_panel" ref="accountPanelHome" :loc="loc" :selected-language-code="currentLanguageCode" :eggs="eggs" :languages="languages" :currentLangOptions="locLanguage" :show-corner-buttons="ui.showCornerButtons" :show-bottom="true" :photo-url="photoUrl" :is-anonymous="isAnonymous" :is-of-age="isOfAge" :show-targeted-ads="showTargetedAds" :current-screen="showScreen" :screens="screens" :isEggStoreSale="isEggStoreSaleItem" :is-subscriber="isSubscriber" @sign-in-clicked="onSignInClicked" @sign-out-clicked="onSignOutClicked" :is-twitch="twitchLinked"></account-panel>
	<div id="main-content" class="main-content display-grid height-100vh">
		<aside class="main-aside">
			<main-sidebar :loc="loc" :player-name="playerName" :menu-items="ui.mainMenu" :current-screen="showScreen" :screens="screens" :mode="equipMode" :current-mode="currentEquipMode" :is-game-paused="game.isPaused" :in-game="game.on" :picked-game-type="currentGameType"></main-sidebar>
			<house-ad v-show="showScreen !== screens.game" :loc="loc" :upgrade-name="upgradeName" :is-event="ui.isEvent" :is-upgraded="isUpgraded" :is-subscriber="isSubscriber" :has-mobile-reward="hasMobileReward" :event-data="ui.eventData" :is-poki="isPoki" :chw-count="chicknWinnerCounter" :chw-ready="showAdBlockerVideoAd" :chw-limit-reached="chicknWinnerDailyLimitReached" @chw-video-request="showNuggyPopup"></house-ad>
			<div ref="chw-home-timer" style="display: none" v-show="!isPoki && firebaseId && showScreen !== screens.game" class="chw-home-timer display-grid grid-column-1-2 grid-align-items-center box_absolute grid-gap-1" :class="chwHomeTimerCls">
				<div>
					<img class="chw-home-timer-chick" :src="chwChickSrc">
				</div>
				<div>
					<div class="display-grid grid-align-items-center bg_white chw-circular-timer-container box_relative gap-sm" :class="chwClass">
						<div v-show="chwShowTimer" class="chw-home-screen-timer"></div>
						<!-- #chw-circular-timer-outer -->
						<div>
							<p class="chw-circular-timer-countdown nospace">
								<span class="chw-pie-remaining text-center chw-msg chw-r-msg" v-html="remainingMsg"></span>
								<span v-show="chwShowTimer" class="chw-pie-num chw-pie-mins"></span><span v-show="chwShowTimer" class="chw-pie-num chw-pie-secs"></span>
							</p>
							<button v-if="chicknWinnerReady && !hasChwPlayClicked && !isChicknWinnerError" class="ss_button btn_sm btn_yolk bevel_yolk" @click="playIncentivizedAd">{{ playAdText }}</button>
						</div>
					</div>
					<div class="speech-tail"></div>
					<div class="chw-circular-timer-container-shadow"></div>
				</div>
			</div>
			<!-- .chw-timer -->
		</aside>
		<!-- .chw-home-timer -->
		<main id="mainScreens">
			<div id="paper_doll_container" class="paper-doll-click-container centered z-index-1" v-show="showScreen === screens.home || showScreen === screens.equip"></div>
			<home-screen id="home_screen" ref="homeScreen" v-show="(showScreen === screens.home || showScreen === screens.profile)"></home-screen>
			<equip-screen id="equip_screen" class="height-100vh" ref="equipScreen" v-show="showEquipScreens"></equip-screen>
			<game-screen id="game_screen" ref="gameScreen" v-show="(showScreen === screens.game)" :kname="killName" :kdname="killedName"></game-screen>
		</main>
	</div>
	<div id="gameDescription" v-show="(showScreen === screens.home)">
		<h1 class="text-center">{{ loc.home_desc_about }}</h1>
		<p class="text-center">{{ loc.home_desc_pick }}
		<svg class="eggIcon"><use xlink:href="#icon-egg"></use></svg>
		{{ loc.home_desc_loadout }}
		<svg class="eggIcon"><use xlink:href="#icon-egg"></use></svg>
		{{ loc.home_desc_madeof }}</p>
		<section class="text-center">
			<p>{{ loc.home_blocked_start }} geometry.monster {{ loc.home_blocked_end }}</p>
		</section>
		<div class="display-grid grid-column-2-eq gap-1 ">
			<section>
				<p>{{ loc.home_desc_p1 }}</p>
				<p>
					<img src="img/eggPose05.png" style="width: 350px; float: left; margin-right: 1em; shape-outside: polygon(0% 0%, 100% 0%, 100% 41%, 84% 48%, 80% 63%, 59% 74%, 46% 100%, 0% 99%);">{{ loc.home_desc_p2 }}
				</p>
			</section>
			<section>
				<p>
					<img src="img/eggPose01.png" style="float: right; margin-left: 1em; margin-top: 1em; shape-outside: polygon(1% 0%, 100% 1%, 100% 99%, 50% 100%, 28% 86%, 16% 68%, 14% 51%, 0 35%);">
					{{ loc.home_desc_p3 }} <br /><br />
					{{ loc.home_desc_p4 }}
				</p>
			</section>
		</div>
		<section>
			<header>
				<h2 class="text-center">{{ loc.home_game_mode_title }}</h2>
			</header>
			<ul class="display-grid grid-column-2-eq gap-1 ">
				<li v-html="loc.home_game_mode_content_li_1"></li>
				<li v-html="loc.home_game_mode_content_li_2"></li>
				<li v-html="loc.home_game_mode_content_li_3"></li>
				<li v-html="loc.home_game_mode_content_li_4"></li>
			</ul>
		</section>

		<h2 class="text-center">{{ loc.home_desc_controls }}</h2>
		<p class="text-center">{{ loc.home_desc_standard }}</p>

		<ul class="display-grid grid-column-2-eq" style="min-width: 25em;max-width: 35em;margin:0 auto">
			<li> {{ loc.home_desc_control1 }}</li>
			<li> {{ loc.home_desc_control2 }}</li>
			<li> {{ loc.home_desc_control3 }}</li>
			<li> {{ loc.home_desc_control4 }}</li>
			<li> {{ loc.home_desc_control5 }}</li>
			<li> {{ loc.home_desc_control6 }}</li>
			<li> {{ loc.home_desc_control7 }}</li>
			<li> {{ loc.home_desc_control8 }}</li>
		</ul>

		<p class="text-center">
			<button class="ss_button btn_lg btn_blue bevel_blue" @click="openUnblocked">{{ loc.home_unblocked_text }}</button>
		</p>

		<p>
			{{ loc.home_desc_p7 }}
		</p>

		<p align="center"><button class="ss_button btn_yolk bevel_yolk" @click="vueApp.scrollToTop()">{{ loc.home_backtotop }}</button></p>

	</div>
	<!-- #gameDescription -->

    <!-- Popup: Settings -->
    <large-popup id="settingsPopup" ref="settingsPopup" @popup-closed="onSharedPopupClosed" @popup-opened="onSettingsPopupOpened" @popup-x="onSettingsX">
        <template slot="content">
        <settings id="settings" ref="settings" :loc="loc" :settings-ui="settingsUi" :languages="languages" :current-language-code="currentLanguageCode" :show-privacy-options="showPrivacyOptions" @privacy-options-opened="onPrivacyOptionsOpened" :is-from-eu="showPrivacyOptions" :controller-id="controllerId" :controller-type="controllerType" :lang-option="locLanguage" :is-vip="(hideAds || contentCreator)"></settings>
        </template>
    </large-popup>

    <!-- Popup: Privacy Options -->
    <small-popup id="privacyPopup" ref="privacyPopup" hide-cancel="true" @popup-closed="onSharedPopupClosed">
        <template slot="header">{{ loc.p_settings_privacy }}</template>
        <template slot="content">
            <label class="ss_checkbox label"> {{ loc.p_settings_of_age }}
                <input id="ofAgeCheck" type="checkbox" v-model="isOfAge" @change="ofAgeChanged($event)">
                <span class="checkmark"></span>
            </label>

            <label class="ss_checkbox label"> {{ loc.p_settings_target_ads }}
                <input id="targetedAdsCheck" type="checkbox" v-model="showTargetedAds" @change="targetedAdsChanged($event)">
                <span class="checkmark"></span>
            </label>
            <!--
            <input id="ofAgeCheck" type="checkbox" v-model="isOfAge" @change="ofAgeChanged($event)">&nbsp;{{ loc.p_settings_of_age }}<br>
            <input id="targetedAdsCheck" type="checkbox" v-model="showTargetedAds" @change="targetedAdsChanged($event)">&nbsp;<span id="targetedAdsText">{{ loc.p_settings_target_ads }}</span>
            -->
        </template>
        <template slot="confirm">{{ loc.ok }}</template>
    </small-popup>

    <!-- Popup: Help & Feedback -->
    <large-popup id="helpPopup" ref="helpPopup" stop-key-capture="true" @popup-closed="onSharedPopupClosed">
        <template slot="content">
			<help id="help" ref="help" :loc="loc" :account-type="accountStatus" :feedback-type="feedbackType" :open-with-type="feedbackSelected" @resetFeedbackType="resetFeedbackType"></help>
        </template>
    </large-popup>

    <!-- Popup: VIP Help & Feedback -->
    <large-popup id="vipPopup" ref="vipPopup" stop-key-capture="true" @popup-closed="onVipHelpClosed">
        <template slot="content">
            <vip-help id="vip-help" ref="vip-help" :loc="loc" :is-vip="isSubscriber"></help>
        </template>
    </large-popup>

    <!-- Popup: Egg Store -->
    <large-popup id="eggStorePopup" ref="eggStorePopup" stop-key-capture="true" @popup-closed="onSharedPopupClosed" :overlay-close="false">
        <template slot="content">
            <egg-store id="eggStore" ref="eggStore" :loc="loc" :products="eggStoreItems" :sale-event="isSale"></egg-store>
        </template>
    </large-popup>

    <img v-show="blackFridayBanner" class="black-friday-banner" style="display: none" src="img/black-friday-banner.jpg" alt="Black Friday Sale"/>

    <!-- Popup: VIP store -->
    <large-popup id="subStorePopup" ref="subStorePopup" stop-key-capture="true" @popup-closed="onSharedPopupClosed" :overlay-close="true">
        <template slot="content">
            <subscription-store id="shell-subscriptions" ref="shell-subscriptions" :loc="loc" :subs="subStoreItems"></egg-store>
        </template>
    </large-popup>

    <!-- Popup: VIP ended -->
    <small-popup id="vipEnded" ref="vipEnded" stop-key-capture="true" @popup-confirm="showSubStorePopup" @popup-closed="onSharedPopupClosed" :overlay-close="true" class="vip">
        <template slot="content">
            <figure>
                <img src="img/vip-club/vip-club-popup-emblem.png" alt="Shell Shockers VIP">
            </figure>
            <div class="vip-ended-popup">
                Yo! Your VIP subscription has expired! If you'd like to keep your awesome benefits (and your Golden Wings!) then click below to join again!
            </div>
        </template>
        <template slot="confirm">Join again!</template>
        <template slot="cancel">No, i don't like stuff</template>
    </small-popup>

    <!-- Popup: Egg Store single -->
    <large-popup id="popupEggStoreSingle" ref="popupEggStoreSingle" stop-key-capture="true" @popup-closed="onSharedPopupClosed" :overlay-close="false" class="popup-store-single">
        <template slot="content">
            <egg-store-item v-for="item in premiumShopItems" :key="item.sku" :item="item" :loc="loc" :account-set="accountSettled" v-if="eggStorePopupSku && item.sku === eggStorePopupSku"></egg-store-item>
        </template>
    </large-popup>

    <!-- Popup: Unsupported Platform -->
    <large-popup id="unsupportedPlatformPopup" ref="unsupportedPlatformPopup" hide-close="true">
        <template slot="content">
            <h2>{{ loc['unsupported_platform'] }}</h2>
            <div>{{ loc[unsupportedPlatformPopup.contentLocKey] }}</div>
        </template>
    </large-popup>

    <!-- Popup: Missing Features -->
    <large-popup id="missingFeaturesPopup" ref="missingFeaturesPopup" hide-close="true">
        <template slot="content">
            <h2>{{ loc['oh_no'] }}</h2>
            <span>{{ loc['missing_features'] }}</span>
            <ul>
                <li v-for="f in missingFeatures" v-html="f"></li>
            </ul>
            <span>{{ loc['missing_help'] }}</span>
        </template>
    </large-popup>

    <!-- Popup: No Anon -->
    <small-popup id="noAnonPopup" ref="noAnonPopup" @popup-confirm="onNoAnonPopupConfirm" @popup-closed="onSharedPopupClosed">
        <template slot="header">{{ loc.no_anon_title }}</template>
        <template slot="content">
            <div>{{ loc.no_anon_msg1 }}</div>
            <div>{{ loc.no_anon_msg2 }}</div>
        </template>
        <template slot="cancel">{{ loc.cancel }}</template>
        <template slot="confirm">{{ loc.no_anon_signup }}</template>
    </small-popup>

    <!-- Popup: Give Stuff -->
	<give-stuff-popup ref="giveStuffPopup" id="giveStuffPopup" :loc="loc" :give-stuff-popup="giveStuffPopup"></give-stuff-popup>

    <!-- Popup: Open URL -->
    <small-popup id="openUrlPopup" ref="openUrlPopup" @popup-confirm="onOpenUrlPopupConfirm" @popup-closed="onSharedPopupClosed">
        <template slot="header">{{ loc[openUrlPopup.titleLocKey] }}</template>
        <template slot="content">
            <!-- content not loc'd (yet) -->
            {{ openUrlPopup.content }}
        </template>
        <template slot="cancel">{{ loc[openUrlPopup.cancelLocKey] }}</template>
        <template slot="confirm">{{ loc[openUrlPopup.confirmLocKey] }}</template>
    </small-popup>

    <!-- Popup: Changelog -->
    <large-popup id="changelogPopup" ref="changelogPopup" @popup-closed="onSharedPopupClosed">
        <template slot="content">
            <h1 id="popup_title nospace" class="roundme_sm">
                {{ loc.changelog_title }}
            </h1>

            <div class="changelog_content roundme_lg">
				<section v-for="(log, idx) in changelog.current">
					<h3>{{ log.version }} - <i><time>{{ log.date }}</time></i></h3>
					<ul>
						<li v-for="data in log.content" v-html="data"></li>
					</ul>
					<hr class="blue">
				</section>

            </div>
			
            <div id="btn_horizontal">
				<button v-if="changelog.showHistoryBtn" @click="showHistoryChangelogPopup" class="ss_button btn_green bevel_green">{{ loc.more }}</button>
                <button @click="hideChangelogPopup" class="ss_button btn_red bevel_red">{{ loc.close }}</button>
            </div>
        </template>
    </large-popup>

    <!-- Popup: Golden Chicken -->
    <!-- <large-popup id="goldChickenPopup" ref="goldChickenPopup" :overlay-close="false">
        <template slot="content">
            <gold-chicken-popup id="gold_chicken" ref="gold_chicken" :loc="loc"></gold-chicken-popup>
        </template>
    </large-popup> -->

    <!-- Popup: Chicken Nugget -->
    <large-popup id="chicknWinner" ref="chicknWinner" :hide-close="true" :overlay-close="false">
        <template slot="content">
            <chicken-nugget-popup id="chickenNugget" ref="chickenNugget" :loc="loc" :firebase-id="firebaseId" :amount-given="miniEggGameAmount" :ad-unit="displayAd.adUnit.nugget" :chw-ready="chicknWinnerReady" :play-count="chicknWinnerCounter" @chw-start-reward="chwDoIncentivized"></chicken-nugget-popup>
        </template>
    </large-popup>
    
    <!-- Popup: Generic Message -->
    <small-popup id="genericPopup" ref="genericPopup" :popup-model="genericMessagePopup" :hide-cancel="true" @popup-closed="onSharedPopupClosed">
        <template slot="header">{{ loc[genericMessagePopup.titleLocKey] }}</template>
        <template slot="content">{{ loc[genericMessagePopup.contentLocKey] }}</template>
        <template slot="confirm">{{ loc[genericMessagePopup.confirmLocKey] }}</template>
    </small-popup>

    <!-- Popup: Anon warning message -->
    <small-popup v-if="isAnonymous" id="anonWarningPopup" ref="anonWarningPopup" :hide-close="true" :overlay-close="false" @popup-cancel="anonWarningPopupCancel" @popup-confirm="anonWarningPopupConfrim">
        <template slot="header">{{ loc.account_anon_warn_popup_title }}!</template>
        <template slot="content">
            <p v-html="loc.account_anon_warn_paragraph_block"></p>
            <p v-html="loc.account_anon_warn_paragraph_block_two"></p>
        </template>
        <template slot="cancel">{{ loc.account_anon_warn_confirm }}</template>
        <template slot="confirm">{{ loc.sign_in }}</template>
    </small-popup>
    
    <!-- Popup: Need More eggs popup -->
    <small-popup id="needMoreEggsPopup" ref="needMoreEggsPopup" @popup-confirm="showEggStorePopup">
        <template slot="header">{{ loc.p_buy_isf_title }}!</template>
        <template slot="content">
            <p>{{ loc.p_buy_isf_content }}.</p>
        </template>
        <template slot="cancel">{{ loc.p_buy_item_cancel }}</template>
        <template slot="confirm">{{ loc.account_title_eggshop }}</template>
    </small-popup>
    
    <!-- Popup: Firebase Sign In -->
    <large-popup id="firebaseSignInPopup" ref="firebaseSignInPopup" :overlay-close="false">
        <template slot="content">
            <h1 class="nospace">{{ loc.sign_in }}</h1>
            <div id="firebaseui-auth-container"></div>
            <div id="btn_horizontal" class="f_center">
                <button @click="onSignInCancelClicked()" class="ss_button btn_red bevel_red btn_sm">{{ loc.cancel }}</button>
            </div>
        </template>
    </large-popup>

    <small-popup ref="adBlockerPopup" id="adBlockerPopup" :overlay-close="false" hide-confirm="true" hide-cancel="true" hide-close="true">
        <template slot="header">
        We've detected ad blocker!
        </template>
        <template slot="content">
            <p>To <i>avoid</i> this message please turn <i>off</i> ad blocker.</p>
            <h4>Please wait</h4>
            <h3>{{adBlockerCountDown}}</h3>
        </template>   
    </small-popup>

    <!-- Popup: PWA -->
    <small-popup id="pwaPopup" class="pwa-popup" ref="pwaPopup" hide-confirm="true" @popup-closed="onSharedPopupClosed">
        <!-- <template slot="header">{{ loc.p_settings_privacy }}</template> -->
        <template slot="content">
            <p>{{loc.pwa_desc_one}}</p>
            <p>{{loc.pwa_desc_two}}</p>
            <button @click="pwaBtnClick" class="ss_button btn_big btn_green bevel_green btn_height_auto btn-pwa-download">
                <div class="pwa-btn-img-box roundme_lg bg-darkgreen">
                    <img src="favicon192.png" alt="Egg yolk">
                    <i class="fas fa-share" aria-hidden="true"></i>
                </div>
                {{loc.pwa_btn_line_one}}<br/>{{loc.pwa_btn_line_two}}
            </button>
        </template>
        <template slot="cancel">{{loc.pwa_no_thanks}}</template>
    </small-popup>

    <large-popup id="adBlockerVideo" ref="adBlockerVideo" @popup-closed="onSharedPopupClosed" :overlay-close="false" hide-confirm="true" hide-cancel="true" hide-close="true">
        <template slot="content">
            <p class="text-center">{{ loc.ad_blocker_big_popup_title }}<br /> <span v-html="loc.ad_blocker_big_popup_desc"></span></p>
			<img src="img/shellshockers-unite-lg.png" alt="">
            <!-- <house-ad id="house-ad-video-replacement" ref="house-ad-video-replacement" :data="bannerHouseAd" :isshowing="showAdBlockerVideoAd"></house-ad> -->
        </template>
    </large-popup>

    <large-popup ref="mobileAdPopup" id="mobileAdPopup" :overlay-close="true" hide-confirm="true" hide-cancel="true">
        <template slot="content">
            <img src="img/mobile/shell-mobile-popup-bg-qr.png" alt="Get Shell Shockers Mobile app!">
        </template>   
    </large-popup>

    <large-popup ref="kotcInstrucPopup" id="kotcInstrucPopup" :overlay-close="true" hide-confirm="true" hide-cancel="true">
        <template slot="content">
            <img class="kotc-wordmark" src="img/kotc/kotc-wordmark.svg" alt="">
            <div class="kotc-how-to-play-wrapper box_aboslute">
                <h2 class="kotc-how-to-play-title text-center"><span class="roundme_md">{{ loc.home_kotc_popup_how_to }}</span><br />{{ loc.home_play }}!</h2>
                <img src="img/kotc/kotc-arrow.svg" aria-hidden="true">
            </div>
            <div class="display-grid grid-column-2-eq grid-gap-space-lg fullwidth ss_margintop_xxxxl">
                <div class="img-container roundme_lg fullwidth step-one">
                    <div class="fullwidth">
                        <p class="text-center"><span class="sr-only">Step </span>1</p>
                        <h6 class="text-center">{{loc.home_kotc_popup_step_one}}</h6>
                    </div>
                </div>
                <div class="img-container roundme_md fullwidth step-two">
                    <div class="fullwidth">
                        <p class="text-center"><span class="sr-only">Step </span>2</p>
                        <h6 class="text-center">{{loc.home_kotc_popup_step_two}}</h6>
                    </div>
                </div>
            </div>
            <div class="display-grid grid-column-2-eq grid-gap-space-lg roundme_md fullwidth ss_margintop_lg kotc-play-now step-three-wrapper">
                <div class="fullwidth box_relative step-three">
                    <img class="kotc-logo box_aboslute" src="img/kotc/kotc-rooster.svg" alt="The King of the Coop Rooster">

                    <div>
                        <p class="text-center"><span class="sr-only">Step </span>3</p>
                        <h6 class="text-center" v-html="loc.home_kotc_popup_step_three"></h6>
                    </div>
                </div>
                <div class="fullwidth f_col f_space_between">
                    <div class="display-grid grid-column-5-eq kotc-crowns">
                    <img aria-hidden="true" src="img/kotc/kotc-crown.svg" alt="Crowns">
                    <img aria-hidden="true" src="img/kotc/kotc-crown.svg" alt="Crowns">
                    <img aria-hidden="true" src="img/kotc/kotc-crown.svg" alt="Crowns">
                    <img aria-hidden="true" src="img/kotc/kotc-crown.svg" alt="Crowns">
                    <img aria-hidden="true" src="img/kotc/kotc-crown.svg" alt="Crowns">
                    </div>
                    <button class="ss_button btn_big btn_green bevel_green fullwidth" @click="onClickPlayKotcNow"><i class="fa fa-play fa-sm"></i> {{ loc.home_play }}</button>
                </div>
            </div>
        </template>   
    </large-popup>

	<large-popup ref="scavengerHunt" id="scavengerHunt" @popup-closed="onSharedPopupClosed" :overlay-close="true">
		<template slot="content">
			<img src="img/scavenger-800x600.png" alt="Attention: Scavenger hunt!" @click="onClickScavengerPopup">
			<button class="ss_button btn_green bevel_green btn_big ss_margintop_lg" @click="onClickScavengerPopup"> Join Discord</button>
		</template>
	</large-popup>

	<small-popup id="deleteAccountApprovalPopup" ref="deleteAccountApprovalPopup" @popup-confirm="onAccountDelectionConfirmed">
	<!-- <template slot="header">{{ loc.p_settings_privacy }}</template> -->
		<template slot="content">
			<h1 v-html="loc.feedback_account_deletion_title"></h1>
			<p class="text-center">
				<i class="fas fa-exclamation-triangle fa-2x text_red"></i>
			</p>
			<p v-html="loc.feedback_account_deletion_desc_1"></p>
			<p v-html="loc.feedback_account_deletion_desc_2"></p>
		</template>
		<template slot="cancel">{{loc.cancel}}</template>
		<template slot="confirm">{{loc.feedback_account_delection_confirm}}</template>
	</small-popup>

	<!-- Popup: Leave Game Confirm -->
	<small-popup id="leaveGameConfirmPopup" ref="leaveGameConfirmPopup" :overlay-close="false" :hide-close="true" @popup-confirm="onLeaveGameConfirm" @popup-cancel="onLeaveGameCancel" @popup-opened="sharedIngamePopupOpened" @popup-closed="sharedIngamePopupClosed">
		<template slot="header">{{ loc.leave_game_title }}</template>
		<template slot="content">
			<p>{{ loc.leave_game_text }}</p>
		</template>
		<template slot="cancel">{{ loc.no }}</template>
		<template slot="confirm">{{ loc.yes }}</template>
	</small-popup>

	<!-- <div id="kotc-play-kotc" class="kotc-play-kotc display-grid">
		<img class="kotc-play-kotc-watermark" src="img/kotc/kotc-crown-indicator.svg" alt="">
		<img class="kotc-play-kotc-arrow" src="img/kotc/kotc-arrow.svg" aria-hidden="true">
	</div> -->
	<!-- #kotc-play-kotc -->
</div> <!-- End of vue instance div -->


<script>
var vueApp;
var vueData = {
    ready: false,
    accountSettled: false,
    missingFeatures: [],
	showChangelogHistoryBtn: true,

	changelog: {
		version: '',
		current: [],
		history: [],
		showHistoryBtn: true
	},

	onClickSignIn: false,

    firebaseId: null,
    photoUrl: null,
    maskedEmail: null,
    isEmailVerified: false,
    isAnonymous: true,
    showPrivacyOptions: isFromEU,
    isOfAge: false,
    showTargetedAds: false,
    delayTheCracking: false,
    displayAdFunction: Function,
    titleScreenDisplayAd: Function,
    displayAdObject: false,
    hideAds: false,

	feedbackSelected: null,

    isPoki: false,
    isPokiGameLoad: false,
    pokiRewardReady: false,
    isPokiNewRewardTimer: false,
    videoRewardTimers: {
        initial: 300000,
        primary: 420000
    },

    pokiRewNum: 1,


    displayAd: {
        adUnit: {
            home: 'shellshockers_titlescreen',
            nugget: 'shellshockers_chicken_nugget_banner',
            house: 'ShellShockers_LoadingScreen_HouseAds',
			spinner: 'shellshockers_respawn_banner',
			respawn: RESPAWNADUNIT,
			respawnTwo: RESPAWN2ADUNIT
        }
    },

    cGrespawnBannerTimeout: null,
    cGrespawnBannerErrors: 0,

    classIdx: 0,
    playerName: '',
    eggs: 0,
    kills: 0,
    deaths: 0,
    kdr: 0,
    streak: 0,
	accountCreated: null,
	kdrLifetime: 0,
	statsCurrent: {},
	statsLifetime: {},
	eggsSpent: 0,
	eggsSpentMonthly: 0,
    isUpgraded: false,
    upgradeName: '',
    isSubscriber: false,
	regionList: [], // Populated by Matchmaker API
    currentRegionId: null,
	currentRegionLocKey: '',
    currentGameType: 0,
    volume: 0,
   	getMusicVolume: 0.5,

    currentLanguageCode: 'en',

	feedbackType: {
		comment: {id: 0, locKey: 'fb_type_commquest'},
		request: {id: 1, locKey: 'fb_type_request'},
		bug: {id: 2, locKey: 'fb_type_bug'},
		purchase: {id: 3, locKey: 'fb_type_purchase'},
		account: {id: 4, locKey: 'fb_type_account'},
		abuse: {id: 5, locKey: 'fb_type_abuse'},
		other: {id: 6, locKey: 'fb_type_other'},
		delete: {id: 7, locKey: 'fb_type_delete'}
	},

	icon: {
		inventory : 'ico-nav-equipment',
		shop: 'ico-nav-shop',
		invite: 'fas fa-user-friends',
		home: 'ico-nav-home',
		user: 'ico-nav-profile',
		settings: 'fas fa-cog',
		fullscreen: 'fas fa-expand-alt',
		egg: 'fas fa-egg',
		dollar: 'fas fa-dollar-sign'
	},

	showScreen: 0,
	screens: {
		home: 0,
		equip: 1,
		game: 2,
		profile: 3
	},

	currentEquipMode: null,

	equipMode: {
		inventory: 0,
		gear: 1,
		featured: 2,
		skins: 3,
		shop: 4,
	},

    ui: {
        overlayType: {
            none: 0,
            dark: 1,
            light: 2,
        },
        overlayClass: {
            inGame: 'overlay_game'
        },
        team: {
            blue: 1,
            red: 2
        },
        houseAds: {
            small: null,
            big: null
        },
        showCornerButtons: true,

		mainMenu: [
			{
				locKey: 'account_title_home',
				icon: 'ico-nav-home',
				screen: 0,
				mode: [],
				hideOn: [2],
			},
			{
				locKey: 'account_title_profile',
				icon: 'ico-nav-profile',
				screen: 3,
				mode: [],
				hideOn: [],
			},
			{
				locKey: 'p_pause_equipment',
				icon: 'ico-nav-equipment',
				screen: 1,
				mode: [0],
				hideOn: [],
			},
			{
				locKey: 'eq_shop',
				icon: 'ico-nav-shop',
				screen: 1,
				mode: [3, 4, 2],
				hideOn: [],
			},
		],
		profile: {
			statTab: 0,
			statTabClicked: false
		},
		playerListOverflow: false,
		eventData: {
			current: 'twitch-drops',
			event: {
				'kotcPopup': {
					img: 'img/kotc/new-game-mode-king-of-coop.png',
					alt: 'Click to learn how to play King of the Coop!',
					url: '',
				},
				'vipImgSrc': {
					img: 'img/events/vip-club-find-out-more.jpeg',
					alt: 'Manage VIP',
					url: ''
				},
				'mobile': {
					img: 'img/events/mobile-double-eggs-for-kills.png',
					alt: 'Download mobile today!',
					url: 'showGetMobilePopup'
				},
				'black-fryday': {
					img: 'img/store-black-friday/black-fryday-home-screen-ad.png',
					alt: 'BLACK FRYDAY SALE!',
					url: ''
				},
				'helpUkraine': {
					img: 'img/stand-with-ukraine-min.png',
					alt: '',
					url: ''
				},
				'egg-org': {
					img: 'img/egg-org/eggOrg_timeTravel_houseAd2-min.jpg',
					alt: 'EggOrg is back!',
					url: ''
				},
				'twitch-drops': {
					img: 'img/events/TwitchDrops2023-houseAd300x250.png',
					alt: 'Get your drops today!',
					url: ''
				},
				'scavengerHunt': {
					img: 'img/scavenger-536x307.png',
					url: '',
					alt: ''
				},
				'chicknWinner': {
					img: 'img/events/small-house-ad-chw-min.png',
					alt: '',
					imgAlt: '2',
					imgAltTwo: '3',
					url: ''
				},
				'badEgg': {
					img: 'img/events/bad-egg-promo-house-ad.png',
					url: 'https://badegg.io?utm_source=shellshockers&utm_medium=referral&utm_campaign=housead',
					alt: ''
				}
			},
		},
		typeSelectors: [
			{
				img: '#ico-primary',
				type: ItemType.Primary
			},
			{
				img: '#ico-secondary',
				type: ItemType.Secondary
			},
			{
				img: '#ico-stamp',
				type: ItemType.Stamp
			},
			{
				img: '#ico-hat',
				type: ItemType.Hat
			},
			{
				img: '#ico-grenade',
				type: ItemType.Grenade
			},
			{
				img: '#ico-melee',
				type: ItemType.Melee
			}
		],

		socialMedia: {
			footer: [
				{name: 'Facebook', reward: 'Facebook', url: 'https://www.facebook.com/bluewizarddigital', imgPath: 'footer-social-media-bubble-facebook.png', icon: 'fa-facebook', id: 1227, owned: false},
				{name: 'Twitter', reward: 'Twitter', url: 'https://twitter.com/bluewizardgames', imgPath: 'footer-social-media-bubble-twitter.png', icon: 'fa-twitter', id: 1234, owned: false},
				{name: 'Instagram', reward: 'Instagram', url: 'https://www.instagram.com/bluewizardgames/', imgPath: 'footer-social-media-bubble-instagram.png', icon: 'fa-instagram', id: 1219, owned: false},
				{name: 'TikTok', reward: 'tiktok', url: 'https://www.tiktok.com/@bluewizarddigital', imgPath: 'footer-social-media-bubble-tiktok.png', icon: 'fa-tiktok', id: 1208, owned: false},
				{name: 'Discord', reward: 'discord', url: 'https://discord.gg/bluewizard', imgPath: 'footer-social-media-bubble-discord.png', icon: 'fa-discord', id: 1200, owned: false},
				{name: 'Steam', reward: 'Steam', url: 'https://store.steampowered.com/publisher/bluewizard', imgPath: 'footer-social-media-bubble-steam.png', icon: 'fa-steam-symbol', id: 1223, owned: false},
				{name: 'Twitch', reward: 'Twitch', url: 'https://www.twitch.tv/bluewizarddigital', imgPath: 'footer-social-media-bubble-twitch.png', icon: 'fa-twitch', id: 1268, owned: false},
				{name: 'newYolker', reward: '', url: 'https://bluewizard.com/subscribe-to-the-new-yolker', imgPath: '', icon: 'fa-envelope-open-text', id: null, owned: null},
			],
			selected: ''
		},
		isEvent: {
			active: false,
			houseAdImg: '',
			homeBtnImg: '',
			popupImg: '',
			popupBtnLoc: '',

		},
		premiumFeaturedTag: '',
		game : {
			stats: {
				loading: false
			}
		}
    },

	twitchLinked: 0,
	twitchName: '',
    languages: [
            { name: 'English', code: 'en' },
            { name: 'French', code: 'fr' },
            { name: 'German', code: 'de' },
            { name: 'Russian', code: 'ru' },
            { name: 'Spanish', code: 'es' },
            { name: 'Portuguese', code: 'pt' },
            { name: 'Korean', code: 'ko' },
            { name: 'Chinese', code: 'zh' },
            { name: 'Dutch', code: 'nl' }
        ],

	locLanguage: {},
    playTypes: {
        joinPublic: 0,
        createPrivate: 1,
        joinPrivate: 2
    },

    gameTypes: [
        { locKey: 'gametype_ffa', value: 0 },
        { locKey: 'gametype_teams', value: 1 },
        { locKey: 'gametype_ctf', value: 2 },
        { locKey: 'gametype_king', value: 3 }
    ],
    // This makes me mad, but until Vue is put in the clojure with GameType,
    // where it should have been to begin with, HERE IT IS >:(
	gameTypeKeys: [
        'FFA',
        'Teams',
        'Spatula',
        'King'
    ],

    twitchStreams: [],
    youtubeStreams: [],
    newsfeedItems: [
            { message: "Test 1 Lorem ipsum dolor sit amet, consectetur adipiscing elit.", image: "img/ico_news.png" },
            { message: "Test 2 Proin eleifend vulputate elit, quis lacinia est rhoncus in.", image: "img/ico_news.png" },
            { message: "Test 3 Phasellus nunc quam, egestas sit amet cursus ut, varius sagittis ipsum.", image: "img/ico_news.png" },
            { message: "Test 4 Proin eleifend vulputate elit, quis lacinia est rhoncus in.", image: "img/ico_news.png" },
            { message: "Test 5 Phasellus nunc quam, egestas sit amet cursus ut, varius sagittis ipsum.", image: "img/ico_news.png" }
        ],
	maps: [],
    settingsUi: {
		settings: [],
        adjusters: {
            misc: [
                { id: 'volume', locKey: 'p_settings_mastervol', min: 0, max: 1, step: 0.01, value: 1, multiplier: 100 }
            ],
            mouse: [
                { id: 'mouseSpeed', locKey: 'p_settings_mousespeed', min: 1, max: 100, step: 1, value: 30 }
            ],
            gamepad: [
                { id: 'sensitivity', locKey: 'p_settings_sensitivity', min: 1, max: 100, step: 1, value: 30 },
                { id: 'deadzone', locKey: 'p_settings_deadzone', min: 0, max: 1, step: 0.01, value: 0.3, precision: 2 }
            ],
            // music: [
            //     { id: 'musicVolume', locKey: 'p_settings_music_volume', min: 0, max: 1, step: 0.01, value: 0.5,  multiplier: 100 }
            // ],
        },
        togglers: {
            misc: [
                { id: 'holdToAim', locKey: 'p_settings_holdtoaim', value: true },
                { id: 'enableChat', locKey: 'p_settings_enablechat', value: true },
                { id: 'safeNames', locKey: 'p_settings_safenames', value: false },
                { id: 'autoDetail', locKey: 'p_settings_autodetail', value: true },
                { id: 'shadowsEnabled', locKey: 'p_settings_shadows', value: true },
                { id: 'highRes', locKey: 'p_settings_highres', value: false },
                { id: 'hideBadge', locKey: 'p_settings_badge_hide', value: false },
                { id: 'closeWindowAlert', locKey: 'p_settings_close_alert', value: false },
                // { id: 'musicStatus', locKey: 'p_settings_music', value: true }
            ],
            mouse: [
                { id: 'mouseInvert', locKey: 'p_settings_invertmouse', value: false },
				{ id: 'fastPollMouse', locKey: 'p_settings_fastpollmouse', value: false },
            ],
            gamepad: [
                { id: 'controllerInvert', locKey: 'p_settings_invertcontroller', value: false },
            ]
        },
        controls: {
            keyboard: {
                // The ids map to the field names in settings.controls[category]
                game: [
                    { id: 'up', side: 'left', locKey: 'keybindings_forward', value: 'W' },
                    { id: 'down', side: 'left', locKey: 'keybindings_backward', value: 'S' },
                    { id: 'left', side: 'left', locKey: 'keybindings_left', value: 'A' },
                    { id: 'right', side: 'left', locKey: 'keybindings_right', value: 'D' },
                    { id: 'jump', side: 'left', locKey: 'keybindings_jump', value: 'SPACE' },
					{ id: 'melee', side: 'left', locKey: 'keybindings_melee', value: 'F' },
                    { id: 'fire', side: 'right', locKey: 'keybindings_fire', value: 'MOUSE 0' },
                    { id: 'scope', side: 'right', locKey: 'keybindings_aim', value: 'SHIFT' },
                    { id: 'reload', side: 'right', locKey: 'keybindings_reload', value: 'R' },
                    { id: 'swap_weapon', side: 'right', locKey: 'keybindings_swapweapon', value: 'E' },
                    { id: 'grenade', side: 'right', locKey: 'keybindings_grenade', value: 'Q' }
                ],
                spectate: [
                    { id: 'ascend', locKey: 'keybindings_spectate_ascend', value: 'SPACE' },
                    { id: 'descend', locKey: 'keybindings_spectate_descend', value: 'SHIFT' }
                ]
            },
            gamepad: {
                // The ids map to the field names in settings.gamepad[category]
                game: [
                    { id: 'jump', locKey: 'keybindings_jump', value: '0' },
                    { id: 'fire', locKey: 'keybindings_fire', value: '1' },
                    { id: 'scope', locKey: 'keybindings_aim', value: '2' },
                    { id: 'reload', locKey: 'keybindings_reload', value: '3' },
                    { id: 'swap_weapon', locKey: 'keybindings_swapweapon', value: '4' },
                    { id: 'grenade', locKey: 'keybindings_grenade', value: '5' },
					{ id: 'melee', locKey: 'keybindings_melee', value: '6' }
                ],
                spectate: [
                    { id: 'ascend', locKey: 'keybindings_spectate_ascend', value: '1' },
                    { id: 'descend', locKey: 'keybindings_spectate_descend', value: '2' }
                ]
            }
        }
    },

    songChanged: false,

    music: {
        isMusic: false,
        musicJson: 'data/sponsors.json',
        musicSrc: '',
        theAudio: '',
        playing: false,
        sponsors: {},
        sponsor: '',
        currIndex: 0,
        currentTime: 0,
        duration: 0,
        timer: null,
        progress: 0,
        volume: 10,
        hideClass: 'music-widget--fade-out',
        serverTracks: {
            id: '',
            title: '',
            artist: '',
            album: '',
            albumArt: '',
            url: '',
            trackUrltest: 'https://shellshock.io',
            sponsor: '',
            sponsorUrl: '',
        }
    },

    home: {        
        joinPrivateGamePopup: {
            code: '',
            showInvalidCodeMsg: false,
            validate: function () {
                if (this.code.length == 0) {
                    console.log('failed validation');
                    this.showInvalidCodeMsg =true;
                    BAWK.play('ui_reset');
                    return false;
                }
                console.log('passed validation');
                return true;
            },
            reset: function () {
                this.code = '';
                this.showInvalidCodeMsg =false;
            }
        },
    },

    equip: {
        get showingItems () {
            return this._showingItems;
        },
        set showingItems (items) {
            this._showingItems = items;
            for (let t of this.lazyRenderTimeouts) {
                clearTimeout(t);
            }
        },
        lazyRenderTimeouts: [],
        equippedPrimary: null,
        equippedSecondary: null,
        equippedHat: null,
        equippedStamp: null,
        equippedGrenade: null,
        posingHat: null,
        posingStamp: null,
        posingWeapon: null,
        posingGrenade: null,
        posingMelee: null,
        showingWeaponType: ItemType.Primary,
        selectedItemType: ItemType.Primary,
        selectedItem: null,
        _showingItems: [],
        buyingItem: null,
        colorIdx: 0,
        extraColorsLocked: true,
        categoryLocKey: null,
        showSpecialItems: false,
        specialItemsTag: null,
		showUnVaultedItems: [],

        redeemCodePopup: {
            code: '',
            showInvalidCodeMsg: false,
            validate: function () {
                if (this.code.length == 0) {
                    console.log('failed validation');
                    this.showInvalidCodeMsg = true;
                    BAWK.play('ui_reset');
                    return false;
                }
                console.log('passed validation');
                return true;
            },
            reset: function () {
                this.code = '';
                this.showInvalidCodeMsg = false;
            }
        },

        physicalUnlockPopup: {
            item: null
        }
    },

    game: {
		on: false,
		isPaused: true,
        shareLinkPopup: {
            url: ''
        },
        gameType: 0,
        team: 1,
        respawnTime: 0,
        tipIdx: 0,
        isGameOwner: false,
		openPopupId: '',
        pauseScreen: {
            id: 'pausePopup',
            adContainerId: 'pauseAdPlacement',
            classChanged: false,
            wasGameInventoryOpen: false,
			mainContainer: '',
			canvas: '',
        }
    },

    isEvent: false,
    doubleEggWeekendSoon: false,
    doubleEggWeekend: false,
    announcementMessage: null,

    playerActionsPopup: {
        playerId: 0,
        uniqueId: 0,
        isGameOwner: false,
        playerName: '',
        muted: false,
        muteFunc: null,
        bootFunc: null,
        social: false,
        vipMember: false
    },

    giveStuffPopup: {
        titleLoc: '',
        eggs: 0,
        items: [],
        type: ''
    },

    openUrlPopup: {
        url: '',
        titleLocKey: '',
        contentLocKey: '',
        confirmLocKey: 'ok',
        cancelLocKey: 'no_thanks'
    },

    genericMessagePopup: {
        titleLocKey: 'keybindings_right',
        contentLocKey: 'p_popup_chicken_nuggetbutton',
        confirmLocKey: 'ok'
    },

    unsupportedPlatformPopup: {
        titleLocKey: 'unsupported_platform',
        contentLocKey: ''
    },

    windowDimensions: {
        width: 0,
        height: 0,
    },

	bannerAds: {
        bannerElId: '',
    },

    googleAnalytics: {
        isUser: null,
        cat: {
            purchases: 'Purchases',
            purchaseComplete: 'Purchase Complete',
            itemShop: 'Item Shop',
            inventory: 'Inventory',
            playerStats: 'player stats',
            play: 'play game',
            redeem: 'Redeem'
        },
        action : {
            eggShackClick: 'Egg Shack Click',
            eggShackProductClick: 'Egg Shack Product Click',
            purchaseComplete: 'Purchase Complete',
            goldenChickenProductClick: 'Golden Chicken Product Click',
            goldenChickenNuggetClick: 'Golden Chicken Nugget Click',
            shopClick: 'Shop Opened ',
            shopItemClick: 'Shop Item Selected',
            shopItemPopupClick: 'Shop Item Popup Click',
            shopItemPopupBuy: 'Item purchased',
            shopItemNeedMoreEggsPopup: 'Need More Eggs Popup',
            inventorySelected: 'Inventory Item ',
            eggCount: 'Egg Count',
            inventoryTabClick: 'Inventory Opened',
            playGameClick: 'Play Game Click',
            redeemed: 'Redeemed',
            redeemClick: 'Redeem open',
            languageSwitch: 'Language setting change',
            langBeforeUpdate: 'Language before auto detect',
            privateGame: 'Private Game',
            shareGamePopup: 'Share game Popup',
            shareGameCopy: 'Shared game code',
            createGame: 'Created game',
            joinGame: 'Joined game',
            playerLimit: 'Player limit',
            timesPlayed: 'Times played',
            anonymousPopupOpenAuto: 'Anon warning auto opened',
            anonymousPopupOpen: 'Anon warning opened',
            anonymousPopupSignupClick: 'Anon warning Sign in clicked',
            anonymousPopupAgreeClick: 'Anon warning Understood clicked',
            denyAnonUserPopup: 'Deny anon user popup',
            denyAnonUserPopupSignin: 'sign in click',
            faqPopupClick: 'FAQ popup open',
            switchTeams: 'Switched Teams',
            error: 'error',
            signIn: 'Sign in'
        },
        label : {
            signInClick: 'sign in click',
            understood: 'Understood click',
            getMoreEggs: 'Get More Eggs Click',
            waitForGameReadyTimeout: 'waitForGameReady timeout',
            signInAuthFailed: 'authorization failed',
            signInTiming: 'Sign in delay',
            signInCompleted: 'Completed',
            signInOut: 'Signed out',
            signInFailed: '',
            homeToGameLoading: 'Home to game loading',
            loading: 'Loading'
        }
    },

    urlParams: null,
    urlParamSet: null,
    adTagUrl: 'https://pubads.g.doubleclick.net/gampad/ads?iu=/21743024831/ShellShock_Video&description_url=__page-url__&env=vp&impl=s&correlator=&tfcd=0&npa=0&gdfp_req=1&output=vast&sz=640x480&unviewed_position_start=1',

    eggStoreItems: [],
    subStoreItems: [],
    premiumShopItems: [],

    eggStoreReferral:  '',
    eggStoreHasSale:  false,
    eggStorePopupSku:  'egg_pack_small',

    showNugget:  true,
    // isMiniGameComplete:  false,
	miniEggGameAmount:  0,
    showGoldenChicken:  false,
    nugStart:  null,
    nugCounter:  null,
    isBuyNugget:  false,
    adBlockerCountDown:  10,
    controllerType:  'generic',
    controllerId:  '',
    controllerButtonIcons: {
        xbox: [
            'A',
            'B',
            'X',
            'Y',
            'LB',
            'RB',
            'LT',
            'RT',
            'Select',
            'Start',
            '<img class="ss_buttonbind_icon" src="img/controller/button_stickleft.svg">',
            '<img class="ss_buttonbind_icon" src="img/controller/button_stickright.svg">',
            '<img class="ss_buttonbind_icon" src="img/controller/button_dpadup.svg">',
            '<img class="ss_buttonbind_icon" src="img/controller/button_dpaddown.svg">',
            '<img class="ss_buttonbind_icon" src="img/controller/button_dpadleft.svg">',
            '<img class="ss_buttonbind_icon" src="img/controller/button_dpadright.svg">'
        ],
        ps: [
            '<img class="ss_buttonbind_icon" src="img/controller/button_cross.svg">',
            '<img class="ss_buttonbind_icon" src="img/controller/button_circle.svg">',
            '<img class="ss_buttonbind_icon" src="img/controller/button_square.svg">',
            '<img class="ss_buttonbind_icon" src="img/controller/button_triangle.svg">',
            'LB',
            'RB',
            'LT',
            'RT',
            'Select',
            'Start',
            '<img class="ss_buttonbind_icon" src="img/controller/button_stickleft.svg">',
            '<img class="ss_buttonbind_icon" src="img/controller/button_stickright.svg">',
            '<img class="ss_buttonbind_icon" src="img/controller/button_dpadup.svg">',
            '<img class="ss_buttonbind_icon" src="img/controller/button_dpaddown.svg">',
            '<img class="ss_buttonbind_icon" src="img/controller/button_dpadleft.svg">',
            '<img class="ss_buttonbind_icon" src="img/controller/button_dpadright.svg">'
        ],
        switchpro: [
            'B',
            'A',
            'Y',
            'X',
            'LB',
            'RB',
            'LT',
            'RT',
            '-',
            '+',
            '<img class="ss_buttonbind_icon" src="img/controller/button_stickleft.svg">',
            '<img class="ss_buttonbind_icon" src="img/controller/button_stickright.svg">',
            '<img class="ss_buttonbind_icon" src="img/controller/button_dpadup.svg">',
            '<img class="ss_buttonbind_icon" src="img/controller/button_dpaddown.svg">',
            '<img class="ss_buttonbind_icon" src="img/controller/button_dpadleft.svg">',
            '<img class="ss_buttonbind_icon" src="img/controller/button_dpadright.svg">'
        ],
        generic: [
            '0',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '10',
            '11',
            '12',
            '13',
            '14',
            '15'
        ]
    },
    pwaDeferEvent: '',
    blackFridayBanner: false,
	isSale: false,
   	smallHouseAd: {},
    bannerHouseAd: false,
    showAdBlockerVideoAd: false,
    hasMobileReward: false,

    killName: null,
    killedName: null,
    killedByMessage: null,
    killedMessage: null,
	chicknWinnerCounter: 0,
	chicknWinnerReady: false,
	chicknWinnerDailyLimitReached: false,
	isChicknWinnerError: false,
	hasChwPlayClicked: false,
	chwActiveTimer: 6000,
	chwHomeTimer: null,
	chwHomeEl: null,

	contentCreator: false,
	eggOrg: false,
	playClicked: false
}// var vueData = new VueData();
</script>

<!-- Shared tags must come before the screen tags -->
<script id="events-template" type="text/x-template">
    <div v-if="showEvent" id="event-notifications">
        <div class="double-eggs f_row align-items-center">
            <!-- <img v-if="doubleEggWeekendSoon && !doubleEggWeekend" src="img/events/2XEggWeekend_cominSoon.png" alt="Double Egg weekend coming soon"> -->
            <img :src="doubleEggEventUrl" />

        </div>
    </div>
</script>
<script>
    const comp_events = {
        template: '#events-template',
        props: ['show', 'currentScreen', 'screens'],
        created() {
            this.checkForEvents();
        },
        data: function () {
		    return vueData;
        },
        methods: {
            isEventDate(days) {
                const day = new Date().getUTCDay();
                return days.includes(day);
            },
            doubbleEggSoon() {
                return this.doubleEggWeekendSoon = this.isEventDate([4,5]);
            },
            doubleEggOn() {
                const ISEVENT = this.isEventDate([5,6,0]);

                if (!ISEVENT) {
                    return;
                }

                const DATE = new Date();
                let  day = DATE.getUTCDay();
                if (day === 5) {
                    let time = DATE.getUTCHours();
                    if (time >= 20) {
                        return (
                            this.doubleEggWeekend = ISEVENT,
                            this.isEvent = ISEVENT
                        )
                    } else {
                        return (
                            this.doubleEggWeekend = false,
                            this.isEvent = false
                        )
                    }
                } else {
                    return (
                        this.doubleEggWeekend = ISEVENT,
                        this.isEvent = ISEVENT
                    )
                }
            },
            checkEvents() {
                return (
                    this.doubbleEggSoon(),
                    this.doubleEggOn()
                )
            },
            checkForEvents() {
                this.checkEvents();
                if (!this.doubleEggWeekend && this.isEventDate([4,5,6,0])) {
                    setTimeout(() => {
                        return this.checkForEvents();
                    }, 60000);
                }
                return;
            },
        },
		computed: {
			showEvent() {
				return (this.doubleEggWeekendSoon || this.doubleEggWeekend) && this.currentScreen == this.screens.game;
			},
			doubleEggEventUrl() {
				if (true) {
					return 'img/events/2XEggWeekend_cominSoon.png';
				} else if (this.doubleEggWeekend) {
					return 'img/events/2XEggWeekend_onNow.png';
				}
			}
		}
    };
</script><script>
var comp_light_overlay = {
	template: `<transition name="fade">
	<div id="lightOverlay" v-show="show" :class="overlayClass" class="overlay overlay_light"></div>
</transition>`,
	data: function () {
		return {
			show: false,
			overlayClass: '',
		};
	},
};
</script><script>
var comp_dark_overlay = {
	template: `<transition name="fade">
	<div id="darkOverlay" v-show="show" :class="overlayClass" class="overlay overlay_dark"></div>
</transition>`,
	data: function () {
		return {
			show: false,
			overlayClass: '',
		};
	},
};
</script><script id="spinner-overlay-template" type="text/x-template">
	<transition name="fadeout">
		<div v-show="isShowing" class="load_screen align-items-center">
			<h3 class="load_message">{{ header }}</h3>
			<wobbly-egg></wobbly-egg>
			<p class="load_message">{{ footer }}</p>
			<display-ad :hidden="hideAds" id="shellshockers_respawn_banner_spinner" ref="loadingScreenDisplayAd" class="pauseFiller" :ignoreSize="false" :adUnit="adUnit" adSize="728x90" :noRefresh="true"></display-ad>
		</div>
	</transition>
</script>

<script id="wobble-egg-template" type="text/x-template">
    <div id="wobbly-egg">
        <svg viewBox="0 0 240 240" :class="[loadEggcontainer, {noanimate: noAnimate}]" width="240" height="240" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <radialGradient r="0.5" cy="0.4" cx="0.4" id="load_yolkgradient" spreadMethod="pad">
                    <stop stop-color="#fed" offset="0.3"/>
                    <stop stop-color="#fb0" offset="0.32"/>
                    <stop stop-color="#fa0" offset="1"/>
                </radialGradient>
            
                <filter id="load_eggshadow" :class="{noanimate: noAnimate}" x="-30%" y="-30%" width="160%" height="160%" >
                    <feDropShadow dx="0" dy="8" stdDeviation="8" flood-color="#124" flood-opacity="0.3" />
                </filter>
            </defs>
            <g>
                <path filter="url(#load_eggshadow)" :class="[loadEggwhite, {noanimate: noAnimate}]" stroke="#000" id="svg_eggwhite" d="m190.13055,40.86621c30.25552,23.71378 -12.26575,57.24017 0,81.77167c12.26575,24.5315 4.9063,80.13624 -33.52639,82.58939c-38.43269,2.45315 -55.60474,-26.16693 -94.03742,-17.98977c-38.43269,8.17717 -11.44803,-30.25552 -17.98977,-44.97442c-6.54173,-14.7189 -24.5315,-46.60985 -4.9063,-71.14135c9.8126,-12.26575 22.07835,-14.92333 34.95739,-15.02554c12.87904,-0.10221 19.01191,-15.63883 31.27766,-17.68312c12.26575,-2.04429 21.46506,17.58091 33.83303,11.2436c12.36797,-6.3373 35.26403,-20.64735 50.39179,-8.79045z" stroke-width="0" fill="#fff" />
            </g>
            <g>
                <ellipse :class="[loadEggyolk, {noanimate: noAnimate}]" ry="38" rx="38" id="svg_eggyolk" cy="120" cx="120" stroke-width="0" fill="url(#load_yolkgradient)"/>
            </g>
        </svg>
    </div>
</script>
<script>
    var comp_wobbly_egg = {
        template: '#wobble-egg-template',
        props: ['noAnimate'],
        
        data: function () {
            return {
                loadEggyolk: 'load_eggyolk',
                loadEggwhite: 'load_eggwhite',
                loadEggcontainer: 'load_eggcontainer'
            }
        }
    };
</script>
<script>
var comp_spinner_overlay = {
	template: '#spinner-overlay-template',
	components: {
		'wobbly-egg': comp_wobbly_egg
	},
	props: ['loc', 'adblockerbanner', 'hideAds', 'adUnit'],
	
	data: function () {
		return {
			isShowing: false,
			header: '',
			footer: '',
			adIsShowing: false,
		}
	},

	methods: {
		show: function (headerLocKey, footerLocKey) {
			this.header = this.loc[headerLocKey];
			this.footer = this.loc[footerLocKey];
			this.isShowing = true;
		},

		showSpinnerLoadProgress: function (percent) {
			var msg = this.loc['ui_game_loading'];
			this.header = this.loc['building_map'];
			this.footer = '{0}... {1}%'.format(msg, percent);
			this.isShowing = true;
		},

		hide: function () {
			this.isShowing = false;
			this.$emit('close-display-ad');
		},

		hideDisplayAd() {
			this.adIsShowing = false;
			console.log('do it');
		},
		showDisplayAd() {
			this.adIsShowing = true;
		},
		toggleDisplayAd() {
			return this.adIsShowing = this.adIsShowing ? false : true;
		}
	},
};
</script><script id="small-popup-template" type="text/x-template">
	<transition name="fade">
		<div v-show="isShowing" class="popup_window popup_sm roundme_sm centered">
			<div>
				<button v-show="!hideClose" @click="onXClick" class="roundme_sm popup_close clickme"><i class="fas fa-times text_white fa-2x"></i></button>
				<h3 id="popup_title" v-show="!hideHeader" class="roundme_sm shadow_blue4 nospace text_white">
					<slot name="header"></slot>
				</h3>
			</div>
			<div v-show="!hideContent" class="popup_sm_content"><slot name="content"></slot></div>
			<div id="btn_horizontal" class="f_center">
				<button class="ss_button btn_red bevel_red width_sm" v-show="!hideCancel" @click="cancelClick"><slot name="cancel"></slot></button>
				<button class="ss_button btn_green bevel_green width_sm" v-show="!hideConfirm" @click="confirmClick"><slot name="confirm"></slot></button>
			</div>
		</div>
	</transition>
</script>

<script id="large-popup-template" type="text/x-template">
	<transition name="fade">
		<div id="popupPause" v-show="isShowing" class="popup_window popup_lg centered roundme_sm" :class="setOverlayCls">
			<button @click="onXClick" v-show="!hideClose" class="popup_close clickme roundme_sm"><i class="fas fa-times text_white fa-2x"></i></button>
			<slot name="content"></slot>
		</div>
	</transition>
</script>

<script>
// Register popup components globally
Vue.component('small-popup', createPopupComponent('#small-popup-template'));
Vue.component('large-popup', createPopupComponent('#large-popup-template'));

function createPopupComponent(templateId) {
	return { 
		template: templateId,
		props: ['hideHeader', 'hideContent', 'hideClose', 'hideCancel', 'hideConfirm', 'overlayType', 'overlayClass', 'popupModel', 'uiModel', 'stopKeyCapture', 'overlayClose'],
		data: function () {
			return {
				isShowing: false,
				overlays: vueData.ui.overlayType,
				popupId: '',
				removeOverlayClick: ''
			}
		},

		created() {
			this.popupId = this.$attrs && this.$attrs.id;
		},

		destroyed: function() {
			document.removeEventListener('keyup', this.escapeKeyClose);
		},

		methods: {
			setVisible: function (visible) {

				this.isShowing = visible;

				if (this.stopKeyCapture && extern.inGame) {
					if (this.isShowing) {
						extern.releaseKeys();	
					} else {
						extern.captureKeys();
					 }
				}

				if (this.isShowing && this.popupModel && this.popupModel.reset) {
					this.popupModel.reset();
				}

				if (!this.isShowing || this.overlayType === this.overlays.none || this.popupId === 'pausePopup') {
					vueApp.setDarkOverlay(false);
					vueApp.setLightOverlay(false);
				} else {
					vueApp.setDarkOverlay(true);
				}

				if (!this.isShowing) {
					console.log('Closed: ' + this.popupId);
					this.$emit('popup-closed', this.popupId);
					vueApp.gameUiRemoveClassForNoScroll();
					this.cancelEventOverLayClickEscapeClose();
				} else {
					console.log('Opened: ' + this.popupId);
					this.$emit('popup-opened', this.popupId);
					vueApp.scrollToTop();
					vueApp.gameUiAddClassForNoScroll();
					this.outsideClickClose();
				}

				if (!extern.inGame) {
					vueApp.toggleTitleScreenAd();
				}

			},

			toggle: function () {
				this.isShowing = !this.isShowing;

				this.setVisible(this.isShowing);
			},
			
			show: function () {
				this.setVisible(true);
			},

			hide: function () {
				this.setVisible(false);
			},

			close: function () {
				this.setVisible(false);
				console.log('Closing');
			},

			onCloseClick: function () {
				this.close();
				BAWK.play('ui_popupclose');
			},

			onXClick: function () {
				this.$emit('popup-x');
				this.close();
				BAWK.play('ui_popupclose');
			},

			cancelClick: function () {
				this.close();
				this.$emit('popup-cancel');
				BAWK.play('ui_popupclose');
			},

			confirmClick: function () {
				if (this.popupModel && this.popupModel.validate && !this.popupModel.validate()) {
					return;
				}
				this.close();
				this.$emit('popup-confirm');
				BAWK.play('ui_playconfirm');
			},

			outsideClickClose: function() {
				if (this.overlayClose === false) {
					return;
				}
				this.removeOverlayClick = this.handleOutsideClick;
				document.addEventListener('click', this.removeOverlayClick);
				document.addEventListener('keyup', this.escapeKeyClose);
				
			},

			escapeKeyClose: function(e) {
				e.stopPropagation();
				if (e.keyCode === 27 && this.isShowing && this.overlayClose !== false) {
					this.onCloseClick();
				}
			},

			handleOutsideClick: function(e) {
				e.stopPropagation();
				if ( e.target.id.includes('Overlay') ) {
					this.onCloseClick();
				}
			},

			cancelEventOverLayClickEscapeClose: function() {
				if (this.overlayClose === false) {
					return;
				}
				document.removeEventListener('click', this.removeOverlayClick);
				document.removeEventListener('keyup', this.escapeKeyClose);
			}
		},
		computed: {
			setOverlayCls() {
				if (extern.inGame) {
					return 'overlay_game';
				}
			}
		}
	}
}
</script>
<script>
    const SvgIcon = {
		template:
			`<svg :class="cls">
				<use :xlink:href="svgName"></use>
			</svg>`,
		props: ['name', 'cls'],
		// data() {
		// 	return {
		// 	};
		// },
		// methods: {
		// },
		computed: {
			svgName() {
				return `#${this.name}`;
			}
		},
	};
    // Register component globally
    Vue.component('icon', SvgIcon);
</script><template id="comp-ss-button" type="text/x-template">
	<button :id="btnId" :class="cls" class="ss_button"><i v-if="iconLeft" class="iconLeft"></i> {{ locTxt }} <i v-if="iconRight" class="iconRight"></i></button>
</template>

<template id="comp-ss-button-dropdown-template" type="text/x-template">
	<button ref="ssDropDown" :id="btnId" @click="onDropdownClick" class="is-for-play ss_button btn_dropdown btn_big common-box-shadow btn_game_mode bg_blue6 text-left box_relative ss-dropdown-select" :class="clsBtn">
		<h3 class="ss-dropdown-select text_blue3" :class="btnId">{{ locTxt.title }}</h3>
		<p class="game-mode-type ss-dropdown-select text_blue5" :class="btnId">{{ locTxt.subTitle }}</p>
		<span class="open-close centered_y ss-dropdown-select" :class="btnId">
			<i class="fas ss-dropdown-select" :class="[caretDirection, btnId, cartOnOpen]"></i>
		</span>
		<div :class="menuPosClass" class="option-box box_absolute roundme_sm common-box-shadow bg_blue6" v-show="isPromptOpen">
			<ul ref="optionBoxList" class="list-no-style nospace ss-dropdown-select f_col">
				<li v-if="listItems" ref="items" v-for="(g, idx) in listItems" :class="{ 'selected' : selectedItem === g.value || selectedItem === idx || selectedItem === g.id }" class="display-grid gap-sm align-items-center text_blue5 font-nunito" @click="onListItemClick(g, idx)"><div class="f_row align-items-center"><icon v-show="selectedItem === g.value || selectedItem === idx || selectedItem === g.id" class="option-box-checkmark" name="ico-checkmark"></icon></div> {{ listItemTxt(g) }}</li>
				<slot name="dropdown"></slot>
			</ul>
		</div>
	</button>
</template>

<script>
	const createSsBtn = (tempId) => {
		return {
			template: tempId,
			props: ['loc', 'cls', 'locTxt', 'iconRight', 'iconLeft', 'listItems', 'selectedItem', 'menuDown', 'menuPos'],
			data: function() {
				return {
					isPromptOpen: false,
					btnId: 'btn-' + (Math.random() + 1).toString(36).substring(7),
					onClickVal: null,
					caret: {
						up: 'fa-caret-up',
						down: 'fa-caret-down',
						right: 'fa-caret-right',
						rotate: 'fa-rotate-180'
					}
				}
			},		
			methods: {
				onListItemClick(val, idx) {
					if (val.value !== undefined) {
						this.onClickVal = val.value;
					} else if (val.subdom !== undefined) { 
						this.onClickVal = val.id;
					} else {
						this.onClickVal = idx;
					}
					this.$emit('onListItemClick', this.onClickVal);
				},
				onDropdownClick(e) {
					if (!this.isPromptOpen) {
						this.isPromptOpen = true;
						this.$emit('dropdownOpen');
						document.addEventListener('click', this.onOutsideClick);
					} else {
						this.isPromptOpen = false;
						this.$emit('dropdownClosed');
						document.removeEventListener('click', this.onOutsideClick);
					}
				},
				onOutsideClick(e) {
					if (this.isPromptOpen) {			
						if (e.target.classList.contains(this.btnId) === false) {
							this.isPromptOpen = false;
							this.$emit('dropdownClosed');
							document.removeEventListener('click', this.onOutsideClick);
						}
					}
				},
				listItemTxt(val) {
					if (val.name !== undefined) {
						return val.name;
					} else {
						return this.loc[val.locKey];
					}
				}
			},
			computed: {
				menuPosClass() {
					if (this.menuPos === 'bottom') {
						return 'pos-bottom';
					} else if (this.menuPos === 'right') {
						return 'pos-right';
					}
				},
				caretDirection() {
					if (this.menuPos === 'bottom') {
						return this.caret.down;
					} else if (this.menuPos === 'right') {
						return this.caret.right;
					} else {
						return this.caret.up;
					}
				},
				cartOnOpen() {
					if (this.isPromptOpen) {
						return this.caret.rotate;
					} 
				},
				clsBtn() {
					return `${this.cls} ${this.btnId}`;
				},
			},
			watch: {
				isPromptOpen(val) {
					BAWK.play('ui_toggletab');

				}
			}
		};
	}
	Vue.component('ss-button', createSsBtn('#comp-ss-button'));
	Vue.component('ss-button-dropdown', createSsBtn('#comp-ss-button-dropdown-template'));
</script><template id="house-display-ad">
    <transition name="fade">
        <figure v-if="isshowing && data" class="house-wrap">
            <img :src="src" :alt="title" @click="adClicked" />
        </figure>
    </transition>
</template>

<script>
    function createHouseAd() {
        return {
            template: '#house-display-ad',
            props: ['isshowing', 'data'],
            data() {
                return {
                    count: 0
                }
            },
            methods: {
                adClicked() {
                    if ('link' in this.data) {
                        this.data.link = this.data.link + '/?utm_source=shell_shockers&utm_medium=referral&utm_campaign=house-ads';
                    }
                    extern.clickedHouseLink(this.data);
                }
            },
            computed: {
                src() {
                    return dynamicContentPrefix + `data/img/art/${this.data.id}${this.data.imageExt}`;
                },
                alt() {
                    return `${this.data.label} banner image!`;
                },
                link() {
                    return `${this.data.link}/?utm_source=shell_shockers&utm_medium=referral&utm_campaign=house-ads`;
                },
                title() {
                    return `Play ${this.data.label} now!`;
                }

            }
        }
    }

    Vue.component('house-ad', createHouseAd());

</script><script id="display-ad-template" type="text/x-template">
    <div>
        <div v-if="!hidden" v-show="isAdShowing" :id="id" class="display-ad-container" :class="theClass"></div>
        <house-ad :data="houseAdData" :isshowing="isAdShowing && houseAdData && adBlocker"></house-ad>
    </div>
</script>

<script>
	
// Register popup components globally
Vue.component('display-ad', createDisplayAdComponent('#display-ad-template'));

function createDisplayAdComponent(templateId) {
	return { 
		template: templateId,
        props: {
            id: String,
            hidden: Boolean,
            adUnit: String,
            isHidden: Boolean,
            poki: Boolean,
            adSize: String,
            override: Boolean,
            houseAd: Boolean,
            ignoreSize: Boolean,
			noRefresh: Boolean,
        },
		data: function () {
			return {
                isAdShowing: false,
                hideAds: false,
                theAd: '',
                adRefreshCheck: false,
                houseAdData: '',
                adBlocker: false,
				hasShown: false,
			}
		},
        mounted() {
            this.$nextTick(() => {
                this.getTheAd();
            });
            this.override = this.override || false;
        },
		methods: {
            getTheAd() {
                this.theAd = document.getElementById(this.adUnit);
                if (pokiActive || extern.productBlockAds) {
                    return;
                }
                const wrap = document.getElementById(this.id);
                wrap.appendChild(this.theAd);

                if (this.houseAd && !crazyGamesActive && !pokiActive) {
                    googletag.cmd.push(function() { googletag.display('ShellShockers_LoadingScreen_HouseAds') });
                }
            },
			setVisible(visible) {

                if (vueData.isUpgraded) {
                    console.log('Upgrade hides ads');
                    this.isAdShowing = false;
                    return;
                }
				if (extern.productBlockAds) {
                    console.log('Closure hides ads');
                    this.isAdShowing = false;
                    return;
                };

                this.isAdShowing = visible;

				if (!this.isAdShowing) {
                    window.removeEventListener('resize', this.hideAdBasedOnscreenSize);
                    // this.adVisibility();
                    if (pokiActive && this.adSize) PokiSDK.destroyAd(this.$el);
                    return;
				} else {
                    this.triggerAd();
                    setTimeout(() => this.hideAdBasedOnscreenSize(), 500);
                    // this.adVisibility();
                    window.addEventListener('resize', this.hideAdBasedOnscreenSize);
                    return;
                }

            },
			show() {
                if (extern.productBlockAds) return;
                console.log(`display ad ${this.id} showing`);
                this.setVisible(true);
            },
			hide() {
                this.setVisible(false);
                console.log(`display ad ${this.id} hiding`);
            },
            crazyGamesAd(id, size) {
                // Removing delay per CG's request. Refresh rate is restricted server-side
                /*if (this.adRefreshCheck) return;
                this.adRefreshCheck = true;
                setTimeout(() => this.adRefreshCheck = false, 20000);*/

                crazysdk.requestBanner([{
                    containerId: id,
                    size: size
                }]);

            },
            triggerAd() {
                this.adBlocker = extern.adBlocker;

                if (this.adBlocker) {
                    this.adblockerSetup();
                    return;
                }

                if (!pokiActive && !crazyGamesActive && !testCrazy) {

                    if (this.houseAd) {
                        gtagInHouseLoadingBanner();
                        return;
                    }

					if (this.hasShown && this.noRefresh) {
						return;
					}

					this.hasShown = true;
					aiptag.subid = AIPSUBID;
                    aiptag.cmd.display.push(() => aipDisplayTag.display(this.adUnit));
                    return;

                } else if (crazyGamesActive || testCrazy) {

                    // since crazy games requires an array for multiple ads on screen will cancel the respawn
                    // ad calls and call them elsewhere
                    if (this.id === 'shellshockers_respawn_banner_ad' ||
                    this.id === 'shellshockers_respawn_banner_2_ad' ||
					this.id === 'shellshockers_respawn_banner-new_ad') return;


                    this.crazyGamesAd(this.id, this.adSize);
                    return;

                } else if (pokiActive && this.adSize) {
                       PokiSDK.displayAd(this.$el, this.adSize);
                    return;
                }
            },
            toggleAd() {
                if (extern.productBlockAds) {
                    this.isAdShowing = false;
                    return;
                };
                this.$nextTick(() => {
                    if (this.isAdShowing) {
                        return this.isAdShowing = false;
                    }
                    return this.isAdShowing = true;
                });

            },
            adblockerSetup() {
				if (pokiActive) return;

                switch(this.id) {
                    case 'div-gpt-ad-shellshockers-loading-houseads-wrap':
                    case 'shellshockers_respawn_banner_ad':
					case 'shellshockers_respawn_banner-new_ad':
                        this.houseAdData = extern.getHouseAd('bigBanner');
                        break;
                    case 'shellshockers_respawn_banner_2_ad':
                    case 'shellshockers_titlescreen_wrap':
                        this.houseAdData = extern.getHouseAd('small');
                    break;
                    default:
                        console.log('House ads say, huh?');
                }

            },
            hideAdBasedOnscreenSize() {
                let adWidth = this.$el.offsetWidth;
                let intViewportWidth = window.innerWidth;

                if (vueApp.displayAdObject && vueApp.displayAdObject > 1 ) {
                    if (vueApp.displayAdObject < 970) {
                        return;
                    }

                    if (vueApp.displayAdObject > intViewportWidth ) {
                        this.hide();
                    }
                } else {
                    if (adWidth < 970) {
                        return;
                    }

                    if (adWidth > intViewportWidth ) {
                        this.hide();
                    }
                }
            },
            adVisibility() {
                if (this.ignoreSize) return;
                googletag.pubads().addEventListener('slotVisibilityChanged', e => {
                        if (e.inViewPercentage < 51) {
                            this.hide();
                        } else {
                            this.show();
                        }
                    }
                );
            }
        },

        computed: {
            theClass() {
                return this.adUnit.toLowerCase().replace(/_/g, "-");
            }
        },
        
        watch: {
            isHidden(value) {
                if (!value) {
                    this.hide();
                }
            },
        }
	}
}
</script>
<!-- include_once('./includes/shared_tags/inc_tag_asc_video_player.php'); -->
<script id="language-selector-template" type="text/x-template">
    <select id="pickLanguage" v-model="languageCode" @change="onChangeLanguage" class="ss_select ss_marginright_sm">
        <option v-for="(language, code) in langOptions" v-bind:value="code">
            {{ language }}
        </option>
    </select>

</script>

<script>
var comp_language_selector = {
    template: '#language-selector-template',
    props: ['languages', 'selectedLanguageCode', 'loc', 'langOptions'],

    data: function () {
        return {
            languageCode: this.selectedLanguageCode,
        }
    },

    methods: {
        playSound (sound) {
			BAWK.play(sound);
        },

		onChangeLanguage: function () {
            vueApp.changeLanguage(this.languageCode);
            // Update localStore for selected language.
            localStore.setItem('languageSelected', this.languageCode);
            BAWK.play('ui_onchange');
            ga('send', 'event', {
                eventCategory: vueData.googleAnalytics.cat.playerStats,
                eventAction: vueApp.googleAnalytics.action.languageSwitch,
                eventLabel: this.languageCode,
            });
		}
    },

    watch: {
        selectedLanguageCode: function (code) {
            this.languageCode = code;
        }
    }
};
</script><script id="gdpr-template" type="text/x-template">
    <transition name="fade">
    <div v-show="isShowing">
        <div id="consent" v-show="showingNotification" class="gdpr_banner f_row">
            <div>{{ loc.gdpr_notification }} <a href="http://www.bluewizard.com/privacypolicy" target="_window">{{ loc.gdpr_link }}</a>
            </div>
            <div class="f_row">
                <button @click="onDisagreeClicked()" class="ss_button btn_red bevel_red ss_marginright ss_marginleft">{{ loc.gdpr_disagree }}</button>
                <button @click="onAgreeClicked()" class="ss_button btn_green bevel_green">{{ loc.gdpr_agree }}</button>
            </div>
        </div>

        <div id="doConsent" v-show="showingConsent" class="gdpr_banner f_row">
            <div>{{ loc.gdpr_consent }}</div>
            <div>
                <button @click="close()" class="ss_button btn_green bevel_green btn_md">{{ loc.ok }}</button>
            </div>
        </div>

        <div id="noConsent"v-show="showingNoConsent" class="gdpr_banner f_row">
            <div>{{ loc.gdpr_noConsent }}</div>
            <div>
                <button @click="close()" class="ss_button btn_green bevel_green btn_md">{{ loc.ok }}</button>
            </div>
        </div>
    </div>
    </transition>
</script>

<script>
var comp_gdpr = {
	template: '#gdpr-template',
	props: ['loc'],

    data: function () {
        return {
            isShowing: false,
            showingNotification: false,
            showingConsent: false,
            showingNoConsent: false
        }
    },

	methods: {
        show: function () {
            this.isShowing = true;
            this.showingNotification = true;
            this.showingConsent = false;
            this.showingNoConsent = false;
        },

        close: function () {
            this.isShowing = false;
            BAWK.play('ui_playconfirm');
        },

        onAgreeClicked: function () {
            this.showingConsent = true;
            this.showingNotification = false;
            extern.doConsent();
            BAWK.play('ui_onchange');
        },

        onDisagreeClicked: function () {
            this.showingNoConsent = true;
            this.showingNotification = false;
            extern.doNotConsent();
            BAWK.play('ui_onchange');
        }
    }
};
</script>
<script id="settings-template" type="text/x-template">
  <div>
  	<h1 class="roundme_sm text-center">{{ loc.p_settings_title }}</h1>

	<div class="display-grid grid-column-3-eq gap-sm">
        <button id="keyboard_button" @click="selectTab" class="ss_bigtab bevel_blue roundme_md font-sigmar f_row align-items-center justify-content-center gap-sm" :class="(showKeyboardTab ? 'selected' : '')"><img src="img/ico_keyboard.svg" class="ss_bigtab_icon"> <img src="img/ico_mouse.svg" class="ss_bigtab_icon"></button>
        <button id="controller_button" @click="selectTab" class="ss_bigtab bevel_blue roundme_md font-sigmar f_row align-items-center justify-content-center gap-sm" :class="(showControllerTab ? 'selected' : '')"><img src="img/ico_gamepad.svg" class="ss_bigtab_icon"></button>
        <button id="misc_button" @click="selectTab" class="ss_bigtab bevel_blue ss_bigtab bevel_blue roundme_md font-sigmar f_row align-items-center justify-content-center gap-sm" :class="(showMiscTab ? 'selected' : '')"><img src="img/ico_monitor.svg" class="ss_bigtab_icon"> <img src="img/ico_speaker.svg" class="ss_bigtab_icon"> <img src="img/ico_privacy.svg" class="ss_bigtab_icon"></button>
    </div>

    <div id="popupInnards" class="roundme_sm fullwidth f_col ss_margintop_sm ss_marginbottom_xl">
		<div id="settings_keyboard" v-show="showKeyboardTab" class="settings-section">
			<h3 class="nospace">{{ loc.p_settings_keybindings }}</h3>
			
			<div class="f_row ss_margintop">
				<div class="f_col">
					<div v-for="c in settingsUi.controls.keyboard.game" v-if="c.side == 'left'" class="nowrap">
						<settings-control-binder :loc="loc" :control-id="c.id" :control-value="c.value" @control-captured="onGameControlCaptured"></settings-control-binder>
						<div class="label">{{ loc[c.locKey] }}</div>
					</div>

					<div class="ss_margintop_xl">
						<div v-for="c in settingsUi.controls.keyboard.spectate" class="nowrap">
							<settings-control-binder :loc="loc" :control-id="c.id" :control-value="c.value" @control-captured="onSpectateControlCaptured"></settings-control-binder>
							<div class="label">{{ loc[c.locKey] }}</div>
						</div>
					</div>
				</div>

				<div class="f_col ss_marginleft_xl">
					<div v-for="c in settingsUi.controls.keyboard.game" v-if="c.side == 'right'" class="nowrap">
						<settings-control-binder :loc="loc" :control-id="c.id" :control-value="c.value" @control-captured="onGameControlCaptured"></settings-control-binder>
						<div class="label">{{ loc[c.locKey] }}</div>
					</div>

					<div class="ss_margintop">
						<div v-for="t in settingsUi.adjusters.mouse" class="nowrap">
							<settings-adjuster :loc="loc" :loc-key="t.locKey" :control-id="t.id" :control-value="t.value" :min="t.min" :max="t.max" :step="t.step" :multiplier="t.multiplier" @setting-adjusted="onSettingAdjusted"></settings-adjuster>
						</div>

						<div v-for="t in settingsUi.togglers.mouse" class="nowrap">
							<settings-toggler v-if="(t.id === 'shadowsEnabled' || t.id === 'highRes') ? showDetailSettings : true" :loc="loc" :loc-key="t.locKey" :control-id="t.id" :control-value="t.value" @setting-toggled="onSettingToggled"></settings-toggler>
						</div>
					</div>

				</div>
			</div>
		</div>
		
		<div id="settings_controller" v-show="showControllerTab" class="settings-section">
			<h3 class="nospace">{{ loc.p_settings_gamepadbindings }}</h3>
			
			<div class="f_row ss_margintop">
				<div class="f_col">
					<div v-for="c in settingsUi.controls.gamepad.game" class="nowrap">
						<settings-gamepad-binder :loc="loc" :control-id="c.id" :control-value="c.value" @control-captured="onGamepadGameControlCaptured" :controller-type="controllerType"></settings-gamepad-binder>
						<div class="label">{{ loc[c.locKey] }}</div>
					</div>
				</div>

				<div class="f_col ss_marginleft_xl">
					<div class="ss_marginbottom_xl">
						<div v-for="c in settingsUi.controls.gamepad.spectate" class="nowrap">
							<settings-gamepad-binder :loc="loc" :control-id="c.id" :control-value="c.value" @control-captured="onGamepadSpectateControlCaptured" :controller-type="controllerType"></settings-gamepad-binder>
							<div class="label">{{ loc[c.locKey] }}</div>
						</div>
					</div>

					<div v-for="t in settingsUi.adjusters.gamepad" class="nowrap">
						<settings-adjuster :loc="loc" :loc-key="t.locKey" :control-id="t.id" :control-value="t.value" :min="t.min" :max="t.max" :step="t.step" :multiplier="t.multiplier" :precision="t.precision" @setting-adjusted="onSettingAdjusted"></settings-adjuster>
					</div>

					<div v-for="t in settingsUi.togglers.gamepad" class="nowrap">
						<settings-toggler v-if="(t.id === 'shadowsEnabled' || t.id === 'highRes') ? showDetailSettings : true" :loc="loc" :loc-key="t.locKey" :control-id="t.id" :control-value="t.value" @setting-toggled="onSettingToggled"></settings-toggler>
					</div>
				</div>
			</div>

			<p>{{ getControllerId }}</p>
			<p>{{ loc.p_settings_controllerhelp }} <a target="_blank" href="https://html5gamepad.com">html5gamepad.com</a></p>
		</div>

		<div id="settings_misc" v-show="showMiscTab" class="settings-section">
			<div class="f_row">
				<div class="f_col">
					<header>
						<h2>{{loc.p_settings_volume_controls}}</h2>
					</header>
					<div v-for="t in settingsUi.adjusters.misc" class="nowrap">
						<settings-adjuster :loc="loc" :loc-key="t.locKey" :control-id="t.id" :control-value="t.value" :min="t.min" :max="t.max" :step="t.step" :multiplier="t.multiplier" @setting-adjusted="onSettingAdjusted"></settings-adjuster>
					</div>
					<div v-for="t in settingsUi.adjusters.music" class="nowrap">
						<settings-adjuster :loc="loc" :loc-key="t.locKey" :control-id="t.id" :control-value="t.value" :min="t.min" :max="t.max" :step="t.step" :multiplier="t.multiplier" @setting-adjusted="onSettingAdjusted"></settings-adjuster>
					</div>

					<h3 class="nospace ss_margintop">{{ loc.p_settings_language }}</h3>
					<language-selector :languages="languages" :loc="loc" :selectedLanguageCode="currentLanguageCode" class="ss_select" :langOptions="langOption"></language-selector>

					<button v-if="showPrivacyOptions" @click="onPrivacyOptionsClicked" class="ss_button btn_blue bevel_blue btn_md ss_margintop_xl">{{ loc.p_settings_privacy }}</button>
				</div>

				<div class="f_col ss_marginleft_xl">
					<div v-for="t in settingsUi.togglers.misc" class="nowrap">
						<settings-toggler v-if="(t.id === 'shadowsEnabled' || t.id === 'highRes') ? showDetailSettings : true" :loc="loc" :loc-key="t.locKey" :control-id="t.id" :control-value="t.value" :hide="hideSetting(t.id)" @setting-toggled="onSettingToggled"></settings-toggler>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="display-grid grid-align-items-center justify-items-strech grid-column-3-eq grid-gap-1 fullwidth gap-sm">
		<button @click="onCloseClick" class="ss_button btn_red bevel_red btn_md no_margin_bottom">{{ loc.cancel }}</button>
		<button @click="onResetClick" class="ss_button btn_yolk bevel_yolk btn_md no_margin_bottom">{{ loc.p_settings_reset }}</button>
		<button @click="onSaveClick" class="ss_button btn_green bevel_green btn_md no_margin_bottom">{{ loc.confirm }}</button>
	</div>

  </div>
</script>

<script id="settings-control-binder-template" type="text/x-template">
	<input ref="controlInput" @change="BAWK.play('ui_onchange')" type="text" v-model="currentValue" :placeholder="loc.press_key" class="ss_keybind clickme" :class="(currentValue === 'undefined' ? 'ss_keybind_undefined' : '')"
		v-on:mousedown="onMouseDown($event)"
		v-on:keydown="onKeyDown($event)" 
		v-on:keyup="onKeyUp($event)" 
		v-on:wheel="onWheel($event)"
		v-on:focusout="onFocusOut($event)">
</script>

<script>
var comp_settings_control_binder = {
	template: '#settings-control-binder-template',
	props: ['loc', 'controlId', 'controlValue'],
	
	data: function () {
		return {
			currentValue: this.controlValue,
			isCapturing: false
		}
	},

	methods: {
		playSound (sound) {
			BAWK.play(sound);
		},
		
		reset: function () {
			this.currentValue = (this.controlValue === null) ? 'undefined' : this.controlValue;
			this.isCapturing = false;
			this.$refs.controlInput.blur();
		},

		capture: function (value) {
			this.isCapturing = false;
			this.$refs.controlInput.blur();
			this.$emit('control-captured', this.controlId, value);
		},

		onMouseDown: function (event) {
			if (!this.isCapturing) {
				this.currentValue = '';
				this.isCapturing = true;
			} else {
				BAWK.play('ui_onchange')
				this.capture('MOUSE ' + event.button);
			}
		},

		onKeyDown: function (event) {
			this.currentValue = '';
			event.stopPropagation();
		},

		onKeyUp: function (event) {
			event.stopPropagation();
			var key = event.key;

			if (key == 'Escape' || key == 'Tab' || key == 'Enter') {
				return;
			}

			if (key == ' ') {
				key = 'space';
				event.preventDefault();
			}
			
			this.capture(key);
		},

		onWheel: function (event) {
			if (this.isCapturing) {
				BAWK.play('ui_onchange')
				if (event.deltaY > 0) {
					this.capture('WHEEL DOWN');
				} else if (event.deltaY < 0) {
					this.capture('WHEEL UP');
				}
			}
		},

		onFocusOut: function (event) {
			this.reset();
		}
	},

	watch: {
		// The value prop gets updated by the parent control; watch for changes and update the backing field of the textbox
		controlValue: function (newValue) {
			this.currentValue = (newValue === null) ? 'undefined' : newValue;
		}
	}
};
</script><script id="settings-gamepad-binder-template" type="text/x-template">
	<button ref="gamepadInput" class="ss_keybind clickme" :class="(currentValue === 'undefined' ? 'ss_keybind_undefined' : '')"
		v-on:mousedown="onMouseDown($event)"
		v-on:keydown="onKeyDown($event)" 
		v-on:keyup="onKeyUp($event)" 
		v-on:focusout="onFocusOut($event)"
		:key="controllerType">
		<span v-html="currentValue"></span></button>
</script>

<script>
var comp_settings_gamepad_binder = {
	template: '#settings-gamepad-binder-template',
	props: ['loc', 'controlId', 'controlValue', 'controllerType'],
	
	data: function () {
		return {
			currentValue: (this.controlValue === null) ? 'undefined' : vueData.controllerButtonIcons[this.controllerType][this.controlValue],
			isCapturing: false
		}
	},

	beforeUpdate: function () {
		if (!this.isCapturing) {
			this.setIcon(this.controlValue);
		}
	},

	methods: {
		playSound (sound) {
			BAWK.play(sound);
		},
		
		reset: function () {
			this.setIcon(this.controlValue);
			this.isCapturing = false;

			removeEventListener('gamepadbuttondown', this.onButtonDown);
			removeEventListener('gamepadbuttonup', this.onButtonUp);
		},

		capture: function (value) {
			this.isCapturing = false;
			this.$refs.gamepadInput.blur();
			this.$emit('control-captured', this.controlId, value);
			this.reset();
		},

		onMouseDown: function (event) {
			if (!this.isCapturing) {
				this.currentValue = this.loc.press_button;
				this.isCapturing = true;

				addEventListener('gamepadbuttondown', this.onButtonDown);
				addEventListener('gamepadbuttonup', this.onButtonUp);
			}
		},

		onKeyDown: function (event) {
			event.stopPropagation();
		},

		onKeyUp: function (event) {
			event.stopPropagation();
		},

		onButtonDown: function (event) {
			if (event.detail == 8 || event.detail == 9) return;
			BAWK.play('ui_onchange')
			this.capture(event.detail);
		},

		onFocusOut: function (event) {
			this.reset();
		},

		setIcon: function (value) {
			this.currentValue = (value === null) ? 'undefined' : vueData.controllerButtonIcons[this.controllerType][value];
		}
	},

	watch: {
		// The value prop gets updated by the parent control; watch for changes and update the backing field of the textbox
		controlValue: function (newValue) {
			this.setIcon(newValue);
		}
	}
};
</script><script id="settings-adjuster-template" type="text/x-template">
	<div>
		<h3 class="nospace">{{ loc[locKey] }}</h3>

		<div class="f_row">
			<input class="ss_slider" type="range" :min="min" :max="max" :step="step" v-model="currentValue" @change="onChange">
			<label class="ss_slider label">{{ getCurrentValue() }}</label>
		</div>
	</div>
</script>

<script>
var comp_settings_adjuster = {
	template: '#settings-adjuster-template',
	props: ['loc', 'locKey', 'controlId', 'controlValue', 'min', 'max', 'step', 'multiplier', 'precision'],

	data: function () {
		return {
			currentValue: this.controlValue
		}
	},

	methods: {
		onChange: function (event) {
			this.$emit('setting-adjusted', this.controlId, this.currentValue);
			BAWK.play('ui_onchange');
		},

		getCurrentValue: function () {
			if (this.precision) {
				return Number.parseFloat(this.currentValue).toFixed(this.precision);
			}
			else {
				return Math.floor(this.currentValue * (this.multiplier || 1));
			}
		}
	},

	watch: {
		// controlValue prop could change when player X's out or clicks Cancel
		controlValue: function (newValue) {
			if (this.currentValue !== newValue) {
				this.currentValue = newValue;
			}
		}
	}
};
</script><script id="settings-toggler-template" type="text/x-template">
	<label v-if="!hide" class="ss_checkbox label"> {{ loc[locKey] }}
		<input type="checkbox" v-model="currentValue" @change="onChange($event)">
		<span class="checkmark"></span>
	</label>
</script>

<script>
var comp_settings_toggler = {
	template: '#settings-toggler-template',
	props: ['loc', 'locKey', 'controlId', 'controlValue', 'hide'],

	data: function () {
		return {
			currentValue: this.controlValue
		}
	},

	methods: {
		onChange: function (event) {
			this.$emit('setting-toggled', this.controlId, this.currentValue);
			BAWK.play('ui_onchange');
		}
	},

	watch: {
		// controlValue prop could change when player X's out or clicks Cancel
		controlValue: function (newValue) {
			if (this.currentValue !== newValue) {
				this.currentValue = newValue;
			}
		}
	}
};
</script>
<script>
var comp_settings = {
	template: '#settings-template',
	components: {
		'settings-control-binder': comp_settings_control_binder,
		'settings-gamepad-binder': comp_settings_gamepad_binder,
		'language-selector': comp_language_selector,
		'settings-adjuster': comp_settings_adjuster,
		'settings-toggler': comp_settings_toggler
	},
	props: ['loc', 'settingsUi', 'languages', 'currentLanguageCode', 'showPrivacyOptions', 'controllerId', 'isFromEU', 'controllerType', 'langOption', 'isVip'],

	data: function () {
		return {
			showKeyboardTab: true,
			showControllerTab: false,
			showMiscTab: false,

			originalSettings: {},
			showDetailSettings: false,
			originalLanguage: '',
			originalMusicVolume: '',
			musicStatChg: ''
		}
	},
	
	methods: {
		selectTab: function (e) {
			return this.switchTab(e.target.id)
		},
		
		switchTab(tab) {

			this.showKeyboardTab = false;
			this.showControllerTab = false;
			this.showMiscTab = false;

			switch (tab) {
				case 'keyboard_button':
					this.showKeyboardTab = true;
					break;

				case 'controller_button':
					this.showControllerTab = true;
					break;

				case 'misc_button':
					this.showMiscTab = true;
					break;
			}

			BAWK.play('ui_toggletab');
		},

		captureOriginalSettings: function () {
			this.originalSettings = deepClone(vueData.settingsUi);
			this.originalLanguage = this.currentLanguageCode;
			// this.originalMusicVolume = this.originalSettings.adjusters.music[0].value;
		},

		applyOriginalSettings: function () {
			vueData.settingsUi = this.originalSettings;
			this.showDetailSettings = !vueData.settingsUi.togglers.misc.find( a => { return a.id === 'autoDetail'; }).value;

			console.log('applying original settings: ' + JSON.stringify(vueData.settingsUi));
		},

		onGameControlCaptured: function (id, value) {
			this.onControlCaptured(this.settingsUi.controls.keyboard.game, id, value)
		},

		onSpectateControlCaptured: function (id, value) {
			this.onControlCaptured(this.settingsUi.controls.keyboard.spectate, id, value)
		},

		onGamepadGameControlCaptured: function (id, value) {
			this.onControlCaptured(this.settingsUi.controls.gamepad.game, id, value)
		},

		onGamepadSpectateControlCaptured: function (id, value) {
			this.onControlCaptured(this.settingsUi.controls.gamepad.spectate, id, value)
		},

		onControlCaptured: function (controls, id, value) {
			value = value.toLocaleUpperCase();

			controls
				.forEach( (c) => {
					if (c.id === id) {
						c.value = value;
					} else {
						if (c.value === value) {
							c.value = null;
						}
					}
			});
		},

		onSettingToggled: function (id, value) {
			console.log('value: ' + value);

			Object.values(this.settingsUi.togglers).forEach(v => {
				var toggler = v.find(t => { return t.id === id; });
				if (toggler) toggler.value = value;
			})

			if (id === 'autoDetail') {
				this.showDetailSettings = !value;
			}

			if (id === 'safeNames') {
				extern.setSafeNames(value);
			}

			// if (id === 'musicStatus') {
			// 	extern.setMusicStatus(value);
			// 	this.musicStatChg = true;

			// 	if (extern.inGame) {
			// 		vueApp.toggleMusic();
			// 	}
			// }
		},

		onSettingAdjusted: function (id, value) {
			Object.values(this.settingsUi.adjusters).forEach(v => {
				var adjuster = v.find( (a) => { return a.id === id; });
				if (adjuster) adjuster.value = value;
			})

			if (id === 'volume') {
				extern.setVolume(value);
			}

			if (id === 'mouseSpeed') {
				extern.setMouseSpeed(value);
			}

			if (id === 'sensitivity') {
				extern.setControllerSpeed(value);
			}

			if (id === 'deadzone') {
				extern.setDeadzone(value);
			}

			if (id === 'musicVolume') {
				extern.setMusicVolume(value);
			}

		},

		onVolumeChange: function () {
			extern.setVolume(this.settingsUi.volume);
		},

		onPrivacyOptionsClicked: function () {
			this.$emit('privacy-options-opened');
			BAWK.play('ui_popupopen');
		},
		
		onCancelClick: function () {
			this.applyOriginalSettings();
			//extern.setMusicVolume(this.originalMusicVolume);
			this.cancelLanguageSelect();
			if (this.musicStatChg) {
				if (extern.inGame) { 
					vueApp.toggleMusic();
				}
			};
			this.$parent.close();
		},

		onCloseClick: function () {
			this.applyOriginalSettings();
			//extern.setMusicVolume(this.originalMusicVolume);
			this.cancelLanguageSelect();
			if (this.musicStatChg) {
				if (extern.inGame) {
					vueApp.toggleMusic();
				}
			};
			vueApp.sharedIngamePopupClosed();
			this.$parent.toggle();
			if (extern.inGame) {
				vueApp.showRespawnDisplayAd();
			}
			BAWK.play('ui_popupclose');
		},

		quickSave() {
			extern.applyUiSettings(this.settingsUi, this.originalSettings);
			this.resetOriginalLanguage();		
		},
		
		onSaveClick: function () {
			// if (vueApp.music.serverTracks.title) {
			// 	this.gaMusicVol();
			// }
			// this.gaMusicVol();

			extern.applyUiSettings(this.settingsUi, this.originalSettings);
			this.resetOriginalLanguage();
			vueApp.sharedIngamePopupClosed();
			this.$parent.toggle();
			BAWK.play('ui_playconfirm');

			ga('send', 'event', 'game', 'settings', 'volume', settings.volume);
			ga('send', 'event', 'game', 'settings', 'mouse speed', settings.mouseSpeed);
			ga('send', 'event', 'game', 'settings', 'mouse invert', settings.mouseInvert);
			ga('send', 'event', 'game', 'settings', 'fast polling mouse', settings.fastPollMouse);
			ga('send', 'event', 'game', 'settings', 'deadzone', settings.deadzone);
			ga('send', 'event', 'game', 'settings', 'controller speed', settings.controllerSpeed);
			ga('send', 'event', 'game', 'settings', 'controller invert', settings.controllerInvert);
		},

		gaMusicVol() {
			let newVol = Number(this.settingsUi.adjusters.music[0].value);
			if (newVol === Number(this.originalMusicVolume)) return;
			if ((Math.round(newVol*100)) <= 1) {
				ga('send', 'event', 'music', 'mute', vueApp.music.serverTracks.title);
			}
		},
		onResetClick: function () {
			extern.resetSettings();
			BAWK.play('ui_reset');
		},
		cancelLanguageSelect: function() {
			this.originalLanguage === vueApp.$data.currentLanguageCode ?
				vueApp.changeLanguage(vueApp.$data.currentLanguageCode) : vueApp.changeLanguage(this.originalLanguage);
			// Revert localStore for language
			localStore.setItem('languageSelected', this.originalLanguage);
			this.resetOriginalLanguage();
		},
		resetOriginalLanguage: function() {
			this.originalLanguage = '';
		},
		setSettings: function (settings) {
			var getSettingById = (list, id) => {
				return list.filter( o => {
						return o.id == id;
				})[0];
			};

			// Keyboard

			getSettingById(this.settingsUi.controls.keyboard.game, 'up').value = settings.controls.keyboard.game.up;
			getSettingById(this.settingsUi.controls.keyboard.game, 'down').value = settings.controls.keyboard.game.down;
			getSettingById(this.settingsUi.controls.keyboard.game, 'left').value = settings.controls.keyboard.game.left;
			getSettingById(this.settingsUi.controls.keyboard.game, 'right').value = settings.controls.keyboard.game.right;
			getSettingById(this.settingsUi.controls.keyboard.game, 'jump').value = settings.controls.keyboard.game.jump;
			getSettingById(this.settingsUi.controls.keyboard.game, 'melee').value = settings.controls.keyboard.game.melee;
			getSettingById(this.settingsUi.controls.keyboard.game, 'fire').value = settings.controls.keyboard.game.fire;
			getSettingById(this.settingsUi.controls.keyboard.game, 'scope').value = settings.controls.keyboard.game.scope;
			getSettingById(this.settingsUi.controls.keyboard.game, 'reload').value = settings.controls.keyboard.game.reload;
			getSettingById(this.settingsUi.controls.keyboard.game, 'swap_weapon').value = settings.controls.keyboard.game.swap_weapon;
			getSettingById(this.settingsUi.controls.keyboard.game, 'grenade').value = settings.controls.keyboard.game.grenade;
			getSettingById(this.settingsUi.controls.keyboard.spectate, 'ascend').value = settings.controls.keyboard.spectate.ascend;
			getSettingById(this.settingsUi.controls.keyboard.spectate, 'descend').value = settings.controls.keyboard.spectate.descend;
			
			// Gamepad

			getSettingById(this.settingsUi.controls.gamepad.game, 'jump').value = settings.controls.gamepad.game.jump;
			getSettingById(this.settingsUi.controls.gamepad.game, 'fire').value = settings.controls.gamepad.game.fire;
			getSettingById(this.settingsUi.controls.gamepad.game, 'scope').value = settings.controls.gamepad.game.scope;
			getSettingById(this.settingsUi.controls.gamepad.game, 'reload').value = settings.controls.gamepad.game.reload;
			getSettingById(this.settingsUi.controls.gamepad.game, 'swap_weapon').value = settings.controls.gamepad.game.swap_weapon;
			getSettingById(this.settingsUi.controls.gamepad.game, 'grenade').value = settings.controls.gamepad.game.grenade;
			getSettingById(this.settingsUi.controls.gamepad.game, 'melee').value = settings.controls.gamepad.game.melee;
			getSettingById(this.settingsUi.controls.gamepad.spectate, 'ascend').value = settings.controls.gamepad.spectate.ascend;
			getSettingById(this.settingsUi.controls.gamepad.spectate, 'descend').value = settings.controls.gamepad.spectate.descend;

			// Misc

			getSettingById(this.settingsUi.adjusters.misc, 'volume').value = settings.volume;
			// getSettingById(this.settingsUi.adjusters.music, 'musicVolume').value = settings.musicVolume;
			getSettingById(this.settingsUi.adjusters.mouse, 'mouseSpeed').value = settings.mouseSpeed;
			getSettingById(this.settingsUi.adjusters.gamepad, 'sensitivity').value = settings.controllerSpeed;
			getSettingById(this.settingsUi.adjusters.gamepad, 'deadzone').value = settings.deadzone;

			getSettingById(this.settingsUi.togglers.mouse, 'mouseInvert').value = (settings.mouseInvert !== 1);
			getSettingById(this.settingsUi.togglers.mouse, 'fastPollMouse').value = settings.fastPollMouse;
			getSettingById(this.settingsUi.togglers.gamepad, 'controllerInvert').value = (settings.controllerInvert !== 1);
			getSettingById(this.settingsUi.togglers.misc, 'holdToAim').value = settings.holdToAim;
			getSettingById(this.settingsUi.togglers.misc, 'enableChat').value = settings.enableChat;
			getSettingById(this.settingsUi.togglers.misc, 'safeNames').value = settings.safeNames;
			getSettingById(this.settingsUi.togglers.misc, 'autoDetail').value = settings.autoDetail;
			getSettingById(this.settingsUi.togglers.misc, 'shadowsEnabled').value = settings.shadowsEnabled;
			getSettingById(this.settingsUi.togglers.misc, 'highRes').value = settings.highRes;
			getSettingById(this.settingsUi.togglers.misc, 'hideBadge').value = settings.hideBadge;
			getSettingById(this.settingsUi.togglers.misc, 'closeWindowAlert').value = settings.closeWindowAlert;
			// getSettingById(this.settingsUi.togglers.misc, 'musicStatus').value = settings.musicStatus;

			console.log('auto detail: ' + settings.autoDetail);
			this.showDetailSettings = !settings.autoDetail;
		},

		hideSetting(id) {
			if (id === 'hideBadge' && !this.isVip) {
				return true;
			}
			return false;
		},

		onHelpClickDelete() {
			vueApp.hideSettingsPopup();
			vueApp.showHelpPopupFeedbackWithDelete();
			ga('send', 'event', vueApp.googleAnalytics.cat.playerStats, vueApp.googleAnalytics.action.faqPopupClick);
			BAWK.play('ui_popupopen');
		},
	},
	computed: {
		getControllerId() {
			if (this.controllerId == 'No controller detected') {
				return vueApp.loc['p_settings_nocontroller']
			} else {
				return this.controllerId
			}
		}
	}
};
</script><script id="help-template" type="text/x-template">
    <div>
       <div class="f_row align-items-center  justify-content-center">
            <button id="faq_button" @click="toggleTabs" class="ss_bigtab bevel_blue ss_marginright roundme_md font-sigmar" :class="(showTab1 ? 'selected' : '')">{{ loc.faq }}</button>
            <button id="fb_button" @click="toggleTabs" class="ss_bigtab bevel_blue roundme_md font-sigmar" :class="(!showTab1 ? 'selected' : '')">{{ loc.feedback }}</button>
        </div>
        <div v-show="showTab1">
            
            <div id="feedback_panel">      

                <h1>{{ loc.faq_title }}</h1>

				<help-questions :content="localizeThis"></help-questions>

                <hr>
                <div id="btn_horizontal" class="f_center">
	            	<button @click="onBackClick" class="ss_button btn_md btn_red bevel_red ss_marginright">{{ loc.cancel }}</button>
                </div>
                
            </div>            
        </div>

        <div v-show="!showTab1">
	        
	        <div id="feedback_panel">
	            <h1 :class="{'text-center' : isAccountDeleteReq}">{{ feedbackTitle }}</h1>
	            
	            <p v-if="!isAccountDeleteReq">{{ loc.fb_feedback_intro }}</p>
				<h4 v-if="isNoAccountForDelete" class="text-center">{{loc.feedback_sign_in_msg}}</h4>

				<div id="btn_horizontal" class="f_center">
	                <select v-model="selectedType" class="ss_field ss_marginright" @click="BAWK.play('ui_click')" @change="BAWK.play('ui_onchange')">
	                    <option v-for="type in feedbackType" :value="type.id">{{ loc[type.locKey] }}</option>
	                </select>
	            
	                <input id="feedbackEmail" v-model="email" :placeholder="loc.fb_email_ph" class="ss_field" v-on:keyup="validateEmail">
				</div>
				
				<div>
	                <textarea v-if="!isAccountDeleteReq" id="feedbackText" class="ss_field" v-model="feedback" :placeholder="loc.fb_feedback_ph" v-on:keyup="validateMessage"></textarea>
				</div>
				
				<div class="f_center f_col">
					<span v-show="emailInvalid" class="ss_marginright error_text">{{ loc.fb_bad_email }}</span>
					<span v-show="messageInvalid" class="ss_marginright error_text">{{ loc.fb_no_comment }}</span>
				</div>
				
	            <div id="btn_horizontal" class="f_center">
	            	<button @click="onBackClick" class="ss_button btn_md btn_red bevel_red ss_marginright">{{ loc.cancel }}</button>
	                <button @click="onSendClick" class="ss_button btn_md btn_green bevel_green">{{ sendBtnText }}</button>
	            </div>
            </div>

        </div>
    </div>
</script>

<script id="help-question-template" type="text/x-template">
    <div>
		<div v-for="qa in content">
			<a :name="qa[0]"></a>
			<h3>{{ qa[1] }}</h3>
			<span v-html="qa[2]"></span>
		</div>
    </div>
</script>

<script>
var comp_help_question = {
    template: '#help-question-template',
    props: ['content'],
};
</script>
<script>
var comp_help = {
    template: '#help-template',
	components: {
        'help-questions': comp_help_question,
	},
	props: ['loc', 'accountType', 'feedbackType', 'openWithType'],
	mounted() {
		this.helpLocSetup();
	},
    data: function () {
        return {
            showTab1: true,
            selectedType: 0,
            email: '',
            feedback: '',
            doValidation: false,
            emailInvalid: false,
            messageInvalid: false,
			qaNum: [1,2,3,4,5,6,7,8,9,10,11],
			newLoc: [],
			localizeThis:  [],
			

        }
    },
    feedbackValidateTimeout: 0,
    methods: {
		playSound (sound) {
			BAWK.play(sound);
        },
        
        validateEmail: function () {
            if (!this.doValidation) {
                return;
            }
            // Insane e-mail-validating regex
            var re = /(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/;
            
            this.emailInvalid = (this.email === '' || !re.test(this.email));
            return !this.emailInvalid;
        },

        validateMessage: function () {
            if (!this.doValidation) {
                return;
            }
            this.messageInvalid = this.feedback === '';
            return !this.messageInvalid;
        },

        toggleTabs: function () {
            this.showTab1 = !this.showTab1;
			this.selectedType = 0;
			this.$emit('resetFeedbackType');
            BAWK.play('ui_toggletab');
			if ( !this.showTab1) {
				ga('send', 'event', 'feedback opened');
			}
        },
        
        onBackClick: function () {
            vueApp.$refs.helpPopup.toggle();
            BAWK.play('ui_popupclose');
        },

		sendFeedbackApi(selected) {
			if (!selected) {
				selected = this.selectedType;
			}

            extern.api_feedback(selected, this.email, this.feedback);

            setTimeout(() => {
                if (this.selectedType !== this.feedbackType.delete.id) {
					this.$parent.toggle();
				}
                this.selectedType = 0;
                this.feedback = null;
                this.email = null;
            }, 900);
		},
		
        onSendClick: function () {
			if (this.isNoAccountForDelete) {
				vueApp.hideHelpPopup();
				vueApp.showFirebaseSignIn();
				return;
			}
            this.doValidation = true;

			if (this.selectedType === this.feedbackType.delete.id) {
				this.comment = null;
				this.$parent.toggle();
				vueApp.showDeleteAccoutApprovalPopup();
				BAWK.play('ui_popupopen');
				return;
			} else {
				if (!this.validateEmail() || !this.validateMessage() ) {
					return;
				}
			}

			BAWK.play('ui_playconfirm');

            // Send that shit out
            this.sendFeedbackApi();
        },

		onAccountDelectionConfirmed() {
			this.sendFeedbackApi(this.feedbackType.delete.id);
		},

		helpLocSetup(locContent) {
			let content = this.loc;
			// if (locContent) {
			// 	content = locContent
			// }
			const locArray = Object.entries(content);
			this.newLoc = locArray.filter( (item, i) => {
				if (item[0].includes('faqItems_q')) {
					return item;
				}
			});

			const tranlateThat = [];

			for (let n = 0; n < this.qaNum.length; n++) {
				tranlateThat.push([]);
				for (let i = 0; i < this.newLoc.length; i++) {
					if (this.newLoc[i][0].includes('faqItems_q' + this.qaNum[n] + '_')) {
						tranlateThat[n].push(this.newLoc[i][1]);
					}
				}
			}
			// Cause once again... vue
			setTimeout(() => {this.localizeThis = tranlateThat}, 500);
		},
		openFeedbackTabWith(type) {
			this.showTab1 = false;
			if (type) {
				this.selectedType = type;
			}
		}
    },
	computed: {
		isAccountDeleteReq() {
			return this.selectedType === this.feedbackType.delete.id;
		},
		isNoAccountForDelete() {
			return this.isAccountDeleteReq && this.accountType == 'no-account';
		},
		sendBtnText() {
			if (this.isNoAccountForDelete) {
				return this.loc.sign_in;
			} else {
				return this.loc.fb_send;
			}
		},
		feedbackTitle() {
			if (!this.isAccountDeleteReq) {
				return this.loc.fb_feedback_title;
			} else {
				return this.loc.fb_delete_account;
			}
		}
	},
	watch: {
		loc(val) {
			this.helpLocSetup(val);
		},
	}
};
</script><script id="vip-help-template" type="text/x-template">
    <div>
        <div id="feedback_panel">      
            <h1>{{ loc.vipHelptitle }}</h1>
            <small class="text_red"><i class="fas fa-exclamation-triangle"></i> {{loc.vipHelpDesc2}}</small>
            <p>{{loc.vipHelpDesc}}</p>

			<div>
				<a :name="loc.vipFaqItems_q1_anchor"></a>
				<h3>{{ loc.vipFaqItems_q1_q }}</h3>
				<p>{{ loc.vipFaqItems_q1_a_1 }}</p>
				<ul>
					<li>{{ loc.vipFaqItems_q1_li_1 }}</li>
					<li>{{ loc.vipFaqItems_q1_li_2 }}</li>
					<li>{{ loc.vipFaqItems_q1_li_3 }}</li>
					<li>{{ loc.vipFaqItems_q1_li_4 }}</li>
					<li>{{ loc.vipFaqItems_q1_li_5 }}</li>
					<li>{{ loc.vipFaqItems_q1_li_6 }}</li>
				</ul>
			</div>

			<div>
				<a :name="loc.vipFaqItems_q2_anchor"></a>
				<h3>{{ loc.vipFaqItems_q2_q }}</h3>
				<p>{{ loc.vipFaqItems_q2_a_1 }}</p>
			</div>

			<div>
				<a :name="loc.vipFaqItems_q3_anchor"></a>
				<h3>{{ loc.vipFaqItems_q3_q }}</h3>
				<p>{{ loc.vipFaqItems_q3_a_1 }}</p>
			</div>

			<div>
				<a :name="loc.vipFaqItems_q4_anchor"></a>
				<h3>{{ loc.vipFaqItems_q4_q }}</h3>
				<p>{{ loc.vipFaqItems_q4_a_mobile_3 }}</p>
				<p>{{ loc.vipFaqItems_q4_a_1 }}</p>
				<p>{{ loc.vipFaqItems_q4_a_2 }}</p>
			</div>

			<div>
				<a :name="loc.vipFaqItems_q5_anchor"></a>
				<h3>{{ loc.vipFaqItems_q5_q }}</h3>
				<p>{{ loc.vipFaqItems_q5_a_1 }}</p>
				<p>{{ loc.vipFaqItems_q5_a_2 }}</p>
				<p>{{ loc.vipFaqItems_q5_a_3 }}</p>
			</div>

			<div>
				<a :name="loc.vipFaqItems_q6_anchor"></a>
				<h3>{{ loc.vipFaqItems_q6_q }}</h3>
				<p>{{ loc.vipFaqItems_q6_a_1 }}</p>
				<p>{{ loc.vipFaqItems_q6_a_2 }}</p>
				<p>{{ loc.vipFaqItems_q6_a_3 }}</p>
			</div>

			<div>
				<a :name="loc.vipFaqItems_q7_anchor"></a>
				<h3>{{ loc.vipFaqItems_q7_q }}</h3>
				<p>{{ loc.vipFaqItems_q7_a_1 }}</p>
			</div>

<!-- 
            <div v-for="qa in loc.vipFaqItems">
                <a :name="qa.anchor"></a>
                <h3>{{ qa.q }}</h3>
                <p v-for="p in qa.a">
                   {{p}}
                </p>
                <ul v-if="qa.li">
                    <li v-for="li in qa.li">{{li}}</li>
                </ul>
            </div> -->

            <hr>
            <div id="btn_horizontal" class="f_center">
                <button @click="openVipStore" class="ss_button btn_md btn_green bevel_green ss_marginright">{{ subButtonTxt }}</button>
                <button @click="onBackClick" class="ss_button btn_md btn_red bevel_red ss_marginright">{{ loc.cancel }}</button>
            </div>

        </div>
    </div>
</script>

<script>
var vip_help = {
    template: '#vip-help-template',
    props: ['loc', 'isVip'],
    data: function () {
        return {

        }
    },
    methods: {
        onBackClick() {
            BAWK.play('ui_popupclose');
            this.$parent.hide();
        },
        openVipStore() {
            this.$parent.hide();
            vueApp.showSubStorePopup();
        }
    },
    computed: {
        subButtonTxt() {
            return this.isVip ? this.loc.sManageBtn : this.loc.account_vip;
        }
    }
};
</script><script id="house-ad-big-template" type="text/x-template">
    <div v-show="(useAd !== null)">
        <button @click="onCloseClicked" class="popup_close splash_ad_close ad_close"><i class="fas fa-times text_white fa-2x"></i></button>
        <img :src="adImageUrl" @click="onClicked" class="splash_ad_image centered roundme_md">
    </div>
</script>

<script>
var comp_house_ad_big = {
    template: '#house-ad-big-template',
    data: function() {
        return {
            removeOverlayClick: '',
        }
    },
    
    props: ['useAd'],

    bigAdTimeout: null,

    methods: {
        onCloseClicked: function () {
            console.log('big ad closed');
            this.close();
        },

        onClicked: function () {
            this.close();
            BAWK.play('ui_click');
            extern.clickedHouseAdBig(this.useAd);
        },

        close: function () {
            if (this.useAd === null) {
				return;
			}
            BAWK.play('ui_popupclose');
            this.$emit('big-house-ad-closed');
        },

        outsideClickClose: function() {
            const showingId = document.getElementById('house-ad-big-template', true);
            this.removeOverlayClick = this.handleOutsideClick;
            document.addEventListener('click', this.removeOverlayClick);
        },
        
        handleOutsideClick: function(e) {
            // Stop bubbling
            e.stopPropagation();
            // If the target does NOT include the class splash_ad_image use the onCloseClicked method and remove the eventListener
            if ( ! e.target.id.includes('splash_ad_image') ) {
                this.onCloseClicked();
                document.removeEventListener('click', this.removeOverlayClick);
            }
        },
    },

    computed: {
        adImageUrl: function () {
            if (!hasValue(this.useAd)) {
                return;
            }

            return dynamicContentPrefix + 'data/img/art/{0}{1}'.format(this.useAd.id, this.useAd.imageExt);
        }
    },

    watch: {
        useAd: function (bigAd) {
            if (hasValue(bigAd)) {
				setTimeout(() => {
					vueApp.hideTitleScreenAd();
				}, 100);
                this.$options.bigAdTimeout = setTimeout(function () {
                    vueApp.ui.houseAds.big = null;
                }, 15000);
                // Close with outside click
                this.outsideClickClose();
            } else {
				vueApp.showTitleScreenAd();
			}
        }
    }
};
</script><script id="house-ad-small-template" type="text/x-template">
    <img v-show="(useAd !== null)" :src="adImageUrl" @click="onClicked" class="news_banner roundme_md">
</script>

<script>
var comp_house_ad_small = {
    template: '#house-ad-small-template',
    
    props: ['useAd'],

    methods: {
        onClicked: function () {
            BAWK.play('ui_click');
            extern.clickedHouseAdSmall(this.useAd);
        }
    },

    computed: {
        adImageUrl: function () {
            if (!hasValue(this.useAd)) {
                return;
            }
            ga('send', 'event', {
				eventCategory: 'House banner ad',
				eventAction: 'show',
				eventLabel: this.useAd.label
			});

            return dynamicContentPrefix + 'data/img/art/{0}{1}'.format(this.useAd.id, this.useAd.imageExt);
        }
    }
};
</script>
<script id="item-template" type="text/x-template">
	<div v-if="active" class="box_relative center_h">
		<div v-show="hasBuyBtn && isSelected && !isItemOwned" class="tool-tip active">
			<span class="paddings_sm">
				<h4 class="nospace text_yolk">{{ tooltipTxt.title }}</h4>
				<p class="nospace text_blue5">{{ tooltipTxt.desc }}</p>
			</span>
		</div>
		<div ref="eggItemInvetory" class="grid-item roundme_sm clickme common-box-shadow box_relative" :class="[itemClass, itemType, itemTagsString]" @click="onClick">
			<span v-if="isVipItem">
				<icon name="ico-vip" class="equip-vip-icon"></icon>
			</span>
			<div v-if="hasBanner" class="equip-item-banner shadow">
				<span class="visibility-hidden">{{bannerTxt}}</span>
			</div>
			<div v-if="hasBanner" class="equip-item-banner">
				{{bannerTxt}}
			</div>
			<div v-if="showPrice" class="equip_smallprice display-grid box_absolute grid-column-auto-1 gap-sm">
				<div class="equip_cost box_absolute f_row align-items-center" :class="{'premium-item-cost' : isPremium}">
					<i v-if="isPremium && !isItemOwned" :class="priceIcon"></i><span v-if="!isPremium && !isItemOwned" class="egg-price-egg"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" xml:space="preserve"><path class="egg-fill" d="M12 21.6c-4.4 0-8-3.5-8-7.8 0-4 3.4-11.4 8.1-11.4 4.8 0 7.8 7.5 7.8 11.4.1 4.3-3.5 7.8-7.9 7.8z" style="fill-rule:evenodd;clip-rule:evenodd;"/><path class="egg-stroke" d="M12.1 3.9c3.6 0 6.3 6.4 6.3 9.9s-2.9 6.3-6.5 6.3-6.5-2.8-6.5-6.3c.1-3.5 3.2-9.9 6.7-9.9m0-3C6.2.9 2.5 9.4 2.5 13.8c0 5.2 4.3 9.3 9.5 9.3s9.5-4.2 9.5-9.3C21.5 9.4 18.2.9 12.1.9z"/></svg>
</span>{{ itemPrice }}
				</div>
			</div>
			<canvas ref="itemCanvas" class="equip_icon centered" :class="canvasCls" width="250" height="250"></canvas>
			<p v-if="showItemOnly" class="item-name text-center font-size-md">{{ item.name }}</p>
		</div>
		<button class="ss_button btn_green bevel_green btn_sm fullwidth ss_margintop box_absolute" v-show="hasBuyBtn && isSelected" @click="onClickBuy">Get it now!</button>
	</div>
</script>

<script>
var comp_item = {
	template: '#item-template',
	props: ['loc', 'item', 'showItemOnly', 'isSelected', 'equippedSlot', 'hasBuyBtn', 'isShop'],

	data: function () {
		return {
			itemOnly: hasValue(this.showItemOnly) ? this.showItemOnly : false,
			active: true,
			itemLimited: false,
			itemUnlock: 'purchase',
			itemTags: [],
			itemTagsString: '',
			premTxt: {
				title: '',
				desc: ''
			}
		}
	},

	mounted() {
		this.prepareItem();
		this.itemHightlightedOrder();
	},

	methods: {
		prepareItem: function () {
			this.itemUnlock = this.item.unlock;

			if (this.itemUnlock == 'premium' && !this.item.activeProduct && this.isShop) {
				this.active = false;
			}

			// We don't need this mess
			if (this.itemUnlock === 'physical' && this.isShop) {
				this.active = false;
			}
			
			this.setUpTags();
			this.isEquippedSlot();
		},
		setUpTags() {
			if (this.item.item_data !== undefined && this.item.item_data.tags !== undefined && this.item.item_data.tags.length) {
				this.item.item_data.tags.forEach(el => this.itemTags.push('item-tag-' + el.toLowerCase().replace(/\s+/g, '-')));
				this.itemTagsString = this.itemTags.toString().replace(/,/g, ' ');
			}
		},

		isEquippedSlot() {
			if (this.equippedSlot) {
				this.active = true;
			}

			vueApp.equip.lazyRenderTimeouts.push(setTimeout(() => this.renderItem(), 0));
		},

		highlightSelected: function () {
			return this.isSelected ? 'highlight' : '';
		},

		itemHightlightedOrder: function() {
			if (this.isPhysicalMerch && vueData.currentEquipMode == vueData.equipMode.shop) return;
			return this.$refs.eggItemInvetory.classList.contains('highlight') ? this.$refs.eggItemInvetory.style.order='-1' : null;
		},

		renderItem() {
			if (this.$refs.itemCanvas === undefined) return;
			extern.renderItemToCanvas(this.item, this.$refs.itemCanvas);
		},

		onClick: function () {
			this.$emit('item-selected', this.item);
		},

		onClickBuy() {
			extern.buyProductForMoney(this.item.sku);
		}

	},

	computed: {
		isItemSellable: function () {
			return (!this.itemOnly && vueData.currentEquipMode == vueData.equipMode.shop) || (!this.itemOnly && vueData.currentEquipMode == vueData.equipMode.skins) || (!this.itemOnly && vueData.currentEquipMode == vueData.equipMode.featured);
		},

		showPrice () {
			return this.isItemSellable && this.item.price > 0;
		},

		isPhysicalMerch () {
			return this.isItemSellable && this.itemUnlock === 'physical';
		},

		isPremium() {
			return this.itemUnlock === 'premium' && !this.itemTags.includes('item-tag-vipitem');
		},

		isVipItem() {
			return this.itemUnlock === 'vip';
		},

		isLimited() {
			return this.itemTags.includes('item-tag-limited');
		},

		isItemOwned() {
			return extern.isItemOwned(this.item);
		},

		hasBanner() {
			return this.isPremium || this.isVipItem || this.isLimited;
		},

		itemType() {
			return getKeyByValue(ItemType, this.item.item_type_id).toLowerCase();
		},

		bannerTxt() {
			if (!this.hasBanner) {
				return;
			} else {
				if (this.isPremium) {
					return this.loc.p_premium_item_banner_txt;
				}
				if (this.isVipItem) {
					return 'VIP';
				}
				if (this.isLimited) {
					return this.loc.eq_limited;
				}
			}
		},

		canvasCls() {
			if (!this.isItemSellable) {
				return 'full-width-height'
			}
		},

		itemClass() {
			return {
				'is-premium': this.isPremium,
				'is-vip': this.isVipItem,
				'highlight': this.isSelected,
			}
		},

		itemPrice() {
			return !this.isItemOwned ? this.item.price : this.loc.eq_owned + '!';
		},

		priceIcon() {
			if (this.isPremium) {
				return vueApp.icon.dollar;
			}
		},
		tooltipTxt() {
			if (this.hasBuyBtn && this.item.sku) {
				this.premTxt.title = vueApp.loc[this.item.sku + '_title'];
				this.premTxt.desc = vueApp.loc[this.item.sku + '_desc'];
			}
			return this.premTxt;
		}
	},

	watch: {
		item: function (val) {
			this.prepareItem();
		}
	}
};
</script><script id="chickn-winner-template" type="text/x-template">
    <div id="popupInnards" class="box_dark roundme_sm fullwidth f_col">
		<header class="display-grid grid-column-1-eq align-items-center roundme_lg">
			<h1 class="chickn-winner-title nospace text-center">{{ loc.p_chicken_header_txt}}</h1>
		</header>
		<section id="chickn-winner-wrapper" class="egg-chick-wrapper f_row roundme_lg box_relative justify-content-center">
			<div v-show="eggGameReady" class="egg-chick-box box_relative" v-for="egg in eggs" :key="egg.id">
				<div v-if="showAmountRewarded && busted && egg.value > 5" class="text-center chw-reward-amount box_absolute">
					<h2 class="box_relative shadow_grey">{{ showAmountRewarded }} <img class="chw-winner-egg" src="img/ico_goldenEgg.svg" /></h2>
				</div>
				<img class="incentivized-egg-chick box_relative" :class="eggClass(egg.value)" @click="checkIfReady" :src="eggSrc(egg.value)" :id="egg.id">
			</div>
			<img v-show="!eggGameReady" class="incentivized-egg-chick centered" :src="chickSrc">
			<button v-show="showAdWatch" class="ss_button btn_large btn_green bevel_green btn_shiny ss_marginbottom_lg" @click="watchVideo">{{ loc.chw_btn_watch_ad }}</button>
			<h2 v-show="showDescTxt" class="centered nospace fullwidth text-center">{{ txtState.desc }}</h2>
			<div v-show="showCountDown" class="chw-progress-bar-wrap-popup roundme_sm box_relative btn_blue bevel_blue bg_blue1 center_h ss_marginbottom_lg">
				<p class="chw-progress-bar-msg box_aboslute centered nospace text-center fullwidth chw-msg chw-p-msg text_white">
					<span class="chw-circular-timer-countdown-popup nospace"><span class="chw-pie-remaining text-center chw-msg chw-r-msg-popup">{{ loc.chw_timer_msg }} </span> <span class="chw-pie-num chw-pie-mins-popup"></span><span class="chw-pie-num chw-pie-secs-popup"></span></span>
				</p>
				<div class="chw-progress-bar-inner-popup bg_blue4"></div>
			</div>
		</section>
		<!-- #chickn-winner-wrapper -->

		<footer class="text-center">
			<button v-show="showPopupBtn" id="gotWinnerOk" @click="onGotWinner" class="ss_button btn_medium btn_yolk bevel_yolk btn_shiny">{{ txtState.btn }}</button>
			<display-ad id="shellshockers_chicken_nugget_banner_ad" ref="nuggetDisplayAd" class="pauseFiller center_h" :ignoreSize="true" :adUnit="adUnit" adSize="728x90"></display-ad>
		</footer>
    </div>
	<!-- #popupInnards -->
</script>

<script>
var comp_chickn_winner_popup = {
    template: '#chickn-winner-template',
    props: ['loc', 'firebaseId', 'amountGiven', 'adUnit', 'chwReady', 'playCount'],

    data: function () {
        return {
			clickedIdx: 0,
			eggs: [{id:'eggOne', value: 0, active: true}, {id:'eggTwo', value: 0, active: true}, {id:'eggThree', value: 0, active: true}],
			isMiniGameComplete: false,
			bustedSrc: `img/incentivized-mini-game/svg/Egg07.svg`,
			busted: false,
			bustedTimer: 1414,
			showEggGame: false,
			clickBeforeReady: false
		}
    },

    methods: {
        placeBannerAdTag: function (tagEl) {
            this.$refs.chickenNuggetAdContainer.appendChild(tagEl);
        },
		showAd() {
			return this.$refs.nuggetDisplayAd.show();
		},
		hideAd() {
			return this.$refs.nuggetDisplayAd.hide();
		},

		eggSrc(count) {
			if (count > 6) {
				return `img/incentivized-mini-game/svg/Egg06.svg`;
			}
			return `img/incentivized-mini-game/svg/Egg0${count}.svg`;
		},

		eggSrcBusted() {
			setTimeout(() => {
				return `img/incentivized-mini-game/svg/Egg07.svg`;
			}, 2424);
		},

		eggBg(count) {
			if (count > 5) {
				return 'incentivized-show'
			}
		},
		eggClass(count) {
			if (count > 5) {
				return 'chick-alive';
			}
		},

		checkIfReady(e) {
			if (this.amountGiven <= 0) {
				this.clickBeforeReady = true;
				vueApp.watchVideo();
			}
				this.eggClickCounter(e);
		},

		eggClickCounter(e) {

			if (!this.busted) {
				BAWK.play('mini-egg-game_shellhit');
			} else {
				BAWK.play('mini-egg-game_chick');
			}

			this.clickedIdx = this.eggs.findIndex(i => i.id === e.target.id);

			let elem = document.getElementById(this.eggs[this.clickedIdx].id);

			if (this.eggs[this.clickedIdx].value < 6) {
				elem.classList.add('chickn-winner-clicked');
				setTimeout(() => elem.classList.remove('chickn-winner-clicked'), 650);
			}

				if (this.eggs[this.clickedIdx].value === 5) {
					this.isMiniGameComplete = true;

					ga('send', 'event', 'Chickn Winner', 'Egg Game', `egg-cracked-${this.eggs[this.clickedIdx].id}`);

					setTimeout(() => {
						this.eggs[this.clickedIdx].active = false;
						elem.src = this.bustedSrc;
					}, this.bustedTimer);

					this.busted = true;

					extern.api_checkBalance();

					this.eggs.forEach(i => {
						if (i.id === e.target.id) {
							return;
						}
						let btn = document.getElementById(i.id).style.pointerEvents = 'none';
						btn.disabled = true;
					});
				}


			if (this.eggs[this.clickedIdx].value === 100) {
				BAWK.play('mini-egg-game_shial');
			}

			this.eggs[this.clickedIdx].value++;
		},

		resetGame() {
			setTimeout(() => {
				this.isMiniGameComplete = false;
				this.busted = false;
				this.eggs.forEach(i => {
					let btn = document.getElementById(i.id).style.pointerEvents = 'all';
					btn.disabled = false;
				});
				vueApp.chwResetAfterWin();
				this.eggs = [{id:'eggOne', value: 0, active: true}, {id:'eggTwo', value: 0, active: true}, {id:'eggThree', value: 0, active: true}];
				this.clickBeforeReady = false;
			}, 1000);
		},

        onGotWinner: function () {
            this.$parent.hide();
			this.hideAd();
			this.resetGame();
			if (this.firebaseId !== null) {
				extern.checkStartChicknWinner(false, true);
			}
			if (extern.inGame) {
				vueApp.disableRespawnButton(true);
				vueApp.showGameMenu();
			}
        },

		watchVideo() {
			vueApp.chwDoIncentivized();
		},
    },
	computed: {
		showAmountRewarded() {
			if (!this.amountGiven) {
				return;
			}

			return `+${this.amountGiven}`;
		},

		txtState() {
			let t = {};
			if (this.imgReadyState) {
				t.desc = this.loc.p_nugget_instruction;
				t.btn = this.loc.p_nugget_button;
			} else {
				t.desc = this.loc.chw_cooldown_msg;
				t.btn = this.loc.ok;
			}

			if (!this.firebaseId) {
				t.desc = this.loc.chw_create_account;
			}

			if (this.showAdWatch) {
				t.btn = this.loc.close;
			}

			if (this.playCount > 3) {
				t.desc = this.loc.chw_daily_limit_msg_two;
			}

			return t;
		},

		limitReached() {
			return this.playCount > 3;
		},

		eggGameReady() {
			return (this.amountGiven && this.firebaseId && !this.limitReached);
		},

		showCountDown() {
			return (!this.amountGiven && this.firebaseId && !this.limitReached && !this.chwReady);
		},

		showAdWatch() {
			return (!this.amountGiven && this.firebaseId && !this.limitReached && this.chwReady);
		},

		showDescTxt() {
			return (!this.chwReady && this.limitReached);
		},

		showPopupBtn() {
			return ((this.busted && this.amountGiven) || this.showCountDown || this.showAdWatch || this.limitReached || !this.firebaseId);
		},

		chickSrc() {
			if (this.limitReached || !this.firebaseId) {
				return 'img/chicken-nugget/chickLoop_daily_limit.svg';
			}
			return 'img/chicken-nugget/chickLoop_sleep.svg';
		},
		
	},
	watch: {
		busted(val) {
			if (val) {
				BAWK.play('mini-egg-game_shellburst');
				setTimeout(() => {
					BAWK.play('mini-egg-game_victory');
				}, 500);
			}
		},
	}
};
</script>

<script id="home-screen-template" type="text/x-template">
	<div>
		<house-ad-big id="big-house-ad" ref="bigHouseAd" :useAd="ui.houseAds.big" @big-house-ad-closed="onBigHouseAdClosed"></house-ad-big>
		<div class="homescreen-main-wrapper display-grid">
			<div class="box_relative fullwidth">
				<profile-screen id="profileScreen" ref="profileScreen" :loc="loc" v-show="(showScreen === screens.profile)" :sign-in-clicked="onSignInClicked" :sign-out-clicked="onSignOutClicked" @leave-game-confimed="leaveGameConfirmed"></profile-screen>
				<play-panel id="play_game" ref="playPanel" v-show="showScreen === screens.home" :show-screen="showScreen" :screens="screen" :loc="loc" :player-name="playerName" :game-types="gameTypes" :current-game-type="currentGameType" :is-game-ready='accountSettled' :region-list="regionList" :current-region-id="currentRegionId" :home="home" :play-clicked="playClicked" :current-class="classIdx" @playerNameChanged="onPlayerNameChanged" @game-type-changed="onGameTypeChanged" :maps="maps"></play-panel>
			</div>
			<aside class="secondary-aside box_relative display-grid justify-content-end">
				<div class="secondary-aside-wrap box_relative">					
					<media-tabs ref="mediaTabs"  :loc="loc" :newsfeedItems="newsfeedItems" :twitchStreams="twitchStreams" :youtubeStreams="youtubeStreams"></media-tabs>
					<display-ad id="shellshockers_titlescreen_wrap" :hidden="hideAds" ref="titleScreenDisplayAd" class="house-small box_absolute" :ignoreSize="true" :adUnit="displayAd.adUnit.home" adSize="300x250"></display-ad>
				</div>
			</aside>
			<!-- <house-ad-small id="banner-ad" :useAd="ui.houseAds.small"></house-ad-small> -->
		</div>
		<div id="mainFooter" class="centered_x">
			<!-- <chicken-panel ref="chickenPanel" id="chicken_panel" :local="loc" :do-upgraded="isUpgraded"></chicken-panel> -->
			<section>
				<footer-links-panel id="footer_links_panel" :loc="loc" :version="changelog.version" :is-poki="isPoki"></footer-links-panel>
			</section>
			<section class="social-icons box_absolute">
				<social-panel ref="socialIconPanel" id="social_panel" :loc="loc" :is-poki="isPoki" :use-social="ui.socialMedia.footer" :social-media="ui.socialMedia.selected"></social-panel>
			</section>
		</div>

		<!-- Popup: Check Email -->
		<small-popup id="checkEmailPopup" ref="checkEmailPopup" :hide-cancel="true">
			<template slot="header">{{ loc.p_check_email_title }}</template>
			<template slot="content">
				<p>{{ loc.p_check_email_text1 }}:</p>
				<h5 class="nospace text-center">{{ maskedEmail }}</h5>
				<p class="ss_marginbottom">{{ loc.p_check_email_text2 }}</p>
			</template>
			<template slot="confirm">{{ loc.ok }}</template>
		</small-popup>

		<!-- Popup: Resend Email -->
		<small-popup id="resendEmailPopup" ref="resendEmailPopup" @popup-confirm="onResendEmailClicked">
			<template slot="header">{{ loc.p_resend_email_title }}</template>
			<template slot="content">
				<p>{{ loc.p_resend_email_text1 }}:</p>
				<h5 class="nospace text-center">{{ maskedEmail }}</h5>
				<p class="ss_marginbottom">{{ loc.p_resend_email_text2 }}</p>
			</template>
			<template slot="cancel">{{ loc.ok }}</template>
			<template slot="confirm">{{ loc.p_resend_email_resend }}</template>
		</small-popup>
		
	</div>
</script>

<script id="create-private-game-template" type="text/x-template">
    <div>
        <div class="roundme_sm fullwidth">
            <div id="popupInnards">
                <div class="create-game-map-select display-grid">
					<h1 class="create-game-header roundme_sm text-center">
						{{ loc.p_privatematch_title }}
					</h1>
					<div class="create-game-map-search box_relative">
						<!-- <input ref="mapSearch" name="name" v-bind:placeholder="loc.p_privatematch_find_map" v-on:keyup="onMapSerachKeyup($event)" class="ss_field font-nunito box_relative"> -->
						<div class="box_relative">
							<label for="search-map" class="centered_y"><i class="fas fa-search text_blue3" :class="[mapSearchResults.length || mapNotFound ? 'fa-times-circle' : 'fa-search']" @click="onMapSearchReset"></i></label>
							<input ref="mapSearch" name="search-map" v-bind:placeholder="loc.p_privatematch_find_map" v-on:keyup="onMapSerachKeyup($event)" @blur="onBlurSearhFocus" class="ss_field font-nunito box_relative">
						</div>
						<div v-show="mapSearchResultsShow" class="option-box box_absolute roundme_sm common-box-shadow bg_blue6 pos-right">
							<ul class="list-no-style nospace ss-dropdown-select f_col">
								<li v-show="!mapSearchResults.length" class="text_blue5 font-nunito" @click="onMapSearchReset">{{ loc.p_privatematch_map_not_found }}</li>
								<li v-for="(item, idx) in mapSearchResults" :key="idx" @click="onMapSearchResultClick(item)" class="text_blue5 font-nunito">
									{{ item.name }}
								</li>
							</ul>
						</div>
					</div>
                    <div id="private_maps" class="roundme_md" :style="{ backgroundImage: 'url(' + mapImgPath + ')' }">
	                    <!-- <img :src="mapImgPath" id="mapThumb" class="roundme_sm text-center"> -->
	                    <div id="mapNav">
	                    	<button id="mapLeft" @click="onMapChange(-1)" class="clickme map-arrows text_white"><i class="fas fa-caret-left fa-3x"></i></button>
		                    <h5 id="mapText" class="text-shadow-black-40">
                                {{ mapList[mapIdx].name }}
                                <span class="map_playercount text-shadow-black-40 font-nunito box_absolute">
									<icon class="map-avg-size-icon fill-white shadow-filter" :name="mapSizeIcon"></icon>
                                </span>
                            </h5>
							<button id="mapRight" @click="onMapChange(1)" class="clickme map-arrows text_white"><i class="fas fa-caret-right fa-3x"></i></button>
	                    </div>
                    </div>
					<div class="hideme">{{ currentRegionId }}</div>
					<ss-button-dropdown class="btn-1 fullwidth" :loc="loc" :loc-txt="gameTypeTxt" :list-items="gameTypes" :selected-item="pickedGameType" menuPos="right" @onListItemClick="onGameTypeChange"></ss-button-dropdown>
					<!-- <ss-button-dropdown :loc="loc" :loc-txt="mapTxt" :list-items="gameTypeMapList" :selected-item="mapIdx" @onListItemClick="onMapChangeClick"></ss-button-dropdown> -->
					<!-- <ss-button-dropdown class="btn-2 fullwidth" :loc="loc" :loc-txt="serverTxt" :list-items="regions" :selected-item="currentRegionId" menuPos="right" @onListItemClick="onServerChange"></ss-button-dropdown> -->
					<ss-button-dropdown class="play-panel-region-select btn-2 fullwidth" :loc="loc" :loc-txt="serverTxt"  :selected-item="currentRegionId" @onListItemClick="onServerChange" menuPos="right">
						<template slot="dropdown">
							<li v-if="regions" ref="items" v-for="(g, idx) in regions" :class="{ 'selected' : currentRegionId === g.id }" class="display-grid gap-1 align-items-center text_blue5 font-nunito regions-select" @click="onServerChange(g.id)">
								<div class="f_row align-items-center">
									<icon v-show="currentRegionId === g.id" name="ico-checkmark" class="option-box-checkmark"></icon>
								</div>
								<div>
									{{ loc[g.locKey ]}}
								</div>
								<div class="text-right">
									{{ g.ping }}ms
								</div>
							</li>
						</template>
					</ss-button-dropdown>
					<!-- <button class="ss_button button_blue bevel_blue fullwidth" @click="onServerClick">{{ loc.server }}: {{ loc[serverLocKey] }}</button> -->
					<button name="play" @click="onPlayClick" class="btn-3 f_row align-items-center gap-sm is-for-play ss_button btn_md text-uppercase font-sigmar fullwidth btn_green bevel_green margin-0">{{ loc.p_privatematch_button }} <icon class="fill-white shadow-filter" name="ico-backToGame"></icon></button>
                </div>
            </div>
        </div>
    </div>
</script>

<script>
var comp_create_private_game_popup = {
    template: '#create-private-game-template',
    props: ['loc', 'regionLocKey', 'mapImgBasePath', 'isGameReady', 'gameTypeTxt', 'gameTypes', 'pickedGameType', 'mapList', 'regions', 'currentRegionId'],

	mounted() {
		this.mapIdx = Math.randomInt(0, this.mapList.length);
		this.map = this.mapList[this.mapIdx];
		this.mapLocKey = this.map.locKey;
        this.mapImgPath = this.mapImgBasePath + this.map.filename + '.png?' + this.map.hash;
		this.onKeyDownMapSelect();
		// this.selectMapForPickedGameType();
	},
    
    data: function () {
        return {
            showingRegionList: false,
            pickedGameType: 0,
            gameTypes: vueData.gameTypes,
            mapIdx: 0,
            playClickedBeforeReady: false,
			map: '',
            mapLocKey: '',
            mapImgPath: '',
			mapNotFound: false,
			mapSearchResults: [],
			mapSearchResultsIdx: 0,
			mapSearchResultsMax: 5,
			mapSearchResultsMin: 0,
			mapSearchResultsShow: false,
			mapSearchIsFocused: false,
            vueData,
        }
    },

    methods: {
        playSound (sound) {
			BAWK.play(sound);
        },
        
		onCloseClick: function () {
            this.$parent.close();
            BAWK.play('ui_popupclose');
        },
        
        onRegionChanged: function () {
            this.showingRegionList = true;
            this.$parent.toggle();
            vueApp.$refs.homeScreen.$refs.playPanel.$refs.pickRegionPopup.toggle();
            BAWK.play('ui_click');
        },

        onMapChange: function (dir) {
            this.selectMapForPickedGameType(dir);
            BAWK.play('ui_onchange');
        },

        selectMapForPickedGameType (dir) {
			let idx = this.mapIdx;

			for (var i = 0; i < vueData.maps.length; i++) { // Prevent race condition
				if (dir) idx = (idx + dir + vueData.maps.length) % vueData.maps.length;
				let map = vueData.maps[idx];
				let gameTypeShortName = vueData.gameTypeKeys[this.pickedGameType];            

				if (map.modes[gameTypeShortName]) {
					break;
				}
				if (dir == 0) dir = 1
				//idx = (idx + dir + vueData.maps.length) % vueData.maps.length;
			}

			this.mapImgPath = this.mapImgBasePath + vueData.maps[idx].filename + '.png?' + vueData.maps[idx].hash;
			this.mapLocKey = vueData.maps[idx].locKey;
			this.mapIdx = idx;
        },

		onKeyDownMapSelect() {
			this.keydownListener = document.addEventListener('keydown', this.handleKeydown, true);
		},

		handleKeydown(e) {
			if (this.mapSearchIsFocused) return;
			this.mapSearchIsFocused = true;
			this.$refs.mapSearch.focus();
		},

		removeKeydown() {
			this.mapSearchIsFocused = false;
			document.removeEventListener('keydown', this.handleKeydown, true);
		},

        onGameTypeChanged () {
            BAWK.play('ui_onchange');
            this.selectMapForPickedGameType(0);
        },

		onPlayTypeWhenSignInComplete() {
			return this.playClickFunction();
		},
		onPlaySentBeforeSignIn() {
			this.gameClickedBeforeReady = true;
			vueApp.showSpinner('signin_auth_title', 'signin_auth_msg');
		},
        onPlayClick: function () {
			this.removeKeydown();
            this.$parent.close();
            if (!this.isGameReady) {
                this.onPlaySentBeforeSignIn();
                return;
            }
            vueApp.externPlayObject(vueData.playTypes.createPrivate, this.pickedGameType, this.vueData.playerName, this.mapIdx, '');
            BAWK.play('ui_playconfirm');
        },
		onGameTypeChange(val) {
			// Not using a select here because it's not possible to style it so we have to update the game mode
			// manually. This is a bit of a hack but it works.
			this.pickedGameType = val;
			// Minor change here. Sending the game type to update global game type change this if we want.
			this.$emit('onGameTypeChange', this.pickedGameType);
			this.selectMapForPickedGameType(0);
		},
		onMapChangeClick(idx) {
			if (idx >= 0) {
				for (var i = 0; i < this.mapList.length; i++) { // Prevent race condition
					let map = this.mapList[idx];
					let gameTypeShortName = vueData.gameTypeKeys[this.pickedGameType];            

					if (map.modes[gameTypeShortName]) {
						break;
					}
				}
				this.mapImgPath = this.mapImgBasePath + this.mapList[idx].filename + '.png?' + this.mapList[idx].hash;
				this.mapLocKey = this.mapList[idx].locKey;
				this.mapIdx = idx;
				BAWK.play('ui_onchange');
			}
		},
		onServerChange(val) {
			this.$emit('onRegionPicked', val);
		},
		onMapSerachKeyup(e) {
			if (this.$refs.mapSearch.value.length >= 1) {
				this.mapSearchResultsShow = true;
				this.mapSearchResults = this.mapList.filter((map, idx) => {
					if (map.name.toLowerCase().replace(/[^a-zA-Z ]/g, "").startsWith(this.$refs.mapSearch.value.toLowerCase())) {
						if (map.modes[vueData.gameTypeKeys[this.pickedGameType]]) {
							if (!this.mapSearchResults.some(map => idx === map.id)) {
								return true;
							}
							return false;
						}
					} else {
						return false;
					}
				});
				if (!this.mapSearchResults.length) {
					this.mapNotFound = true;
				}
			} else {
				this.mapSearchResults.length = 0;
				this.mapSearchResultsShow = false;
				this.mapNotFound = false;
			}

			if (this.mapSearchResults.length === 1) {
				this.onMapChangeClick(this.mapList.findIndex(m => m.filename === this.mapSearchResults[0].filename));
				this.mapSearchResultsShow = false;
				this.mapNotFound = false;
			}
		},
		onMapSearchReset() {
			this.$refs.mapSearch.value = '';
			this.mapSearchResultsShow = false;
			this.mapSearchResults.length = 0;
			this.mapNotFound = false;
			this.mapSearchIsFocused = false;
		},
		onMapSearchResultClick(map) {
			this.onMapSearchReset();
			this.onMapChangeClick(this.mapList.findIndex(m => m.filename === map.filename));
		},
		onBlurSearhFocus() {
			this.mapSearchIsFocused = false;
		}
    },
	computed: {
		serverTxt() {
				let name = '';
				if (hasValue(this.regions) && hasValue(this.currentRegionId)) {
					name = this.regions.filter(s => s.id === this.currentRegionId)[0].locKey;
				}
			return {
				title: this.loc.p_servers_title,
				subTitle: this.loc[name]
			}
		},
		mapSizeIcon() {
			if (this.mapList[this.mapIdx].numPlayers <= 13) {
				return 'ico-map-size-small';
			} else if (this.mapList[this.mapIdx].numPlayers >= 14 && this.mapList[this.mapIdx].numPlayers <= 17) {
				return 'ico-map-size-med';
			} else if (this.mapList[this.mapIdx].numPlayers > 17) {
				return 'ico-map-size-large';
			}
		}
	},
    watch: {
        isGameReady(val) {
            if (this.gameClickedBeforeReady && val) {
                setTimeout(() => this.onPlayClick(), 700);
            }
        },
	}   
};
</script><script id="account-panel-template" type="text/x-template">
	<div>
		<div id="account_top" class="f_row f_end_only account-wrapper align-items-center ">
			<event-panel v-show="showCornerButtons" :current-screen="currentScreen" :screens="screens"></event-panel>
			<!-- <eggstore-notify ref="shirtStore" :show="showCornerButtons" :loc="loc" :sku="sku" icon="fa-tshirt" :text-hide="true" text="p_egg_shop_sale_notify" title="account_threadless" color="blue" url="https://bluewizard.threadless.com/" analytics="threadless"></eggstore-notify> -->
			<!-- <eggstore-notify ref="eggStoreSaleNotify" :text-hide="!hideNewItemNotify" :show="showCornerButtons" :loc="loc" :sku="sku" title="account_premium_item" icon="fa-gem" text="p_egg_shop_sale_notify" analytics="diamond"></eggstore-notify> -->
			<div class="account_eggs roundme_sm clickme f_row align-items-center" @click="onEggStoreClick" v-bind:title="loc['account_title_eggshop']">
				<div class="box_relative">
					<img :src="isAnonymous ? 'img/svg/ico_goldenEgg_callout.svg' : 'img/svg/ico_goldenEgg.svg'" class="egg_icon">
				</div>
				<span ref="eggCounter" class="egg_count">{{ eggBalance }}</span>
			</div>
			<!-- <button v-if="showVipButton" @click="onSubscriptionClick" class="ss_button btn_yolk bevel_yolk btn_vip" :title="loc['account_vip']" :class="{'has-sub' : isVipLive}"><img src="img/vip-club/vip-club-emblem-sm.png" alt="VIP Emblem"> {{vipButtonText}}</button> -->
			<button v-show="showNotInGame" :class="accountBtnCls" @click="onAccountBtnClick" class="ss_button btn-account-status font-sigmar align-items-center text-center justify-content-center">
				<span v-if="!isNotSignedIn">
					<icon name="ico-vip"></icon>
				</span> {{ accountBtnText }}
			</button>
			<!-- <input type="image" src="img/ico_nav_leaderboards.png" class="account_icon roundme_sm"> -->
			<div id="corner-buttons" v-show="showCornerButtons" class="f_row f_end_only align-items-center ">
				{{isPaused}}
				<!-- <input type="image" src="img/ico_nav_shop.png" @click="itemStoreClick" class="account_icon roundme_sm" v-bind:title="loc['account_title_shop']"> -->
				<!-- <input type="image" src="img/ico_nav_help.png" @click="onHelpClick" class="account_icon roundme_sm" v-bind:title="loc['account_title_faq']"> -->
				<button @click="onShareLinkClick" v-show="showShareLinkButton"  class="ss_button btn_blue bevel_blue box_relative pause-screen-ui btn-account-w-icon text-shadow-none text_blue1" :title="loc.p_pause_sharelink"><i :class="vueData.icon.invite"></i></button>
				<button @click="onSettingsClick" class="ss_button btn_blue bevel_blue box_relative pause-screen-ui btn-account-w-icon text-shadow-none text_blue1" :title="loc.account_title_settings"><i :class="vueData.icon.settings"></i></button>
				<button @click="onFullscreenClick" v-if="!vueData.isPoki && !crazyGamesActive" class="ss_button btn_blue bevel_blue box_relative pause-screen-ui btn-account-w-icon text-shadow-none text_blue1" :title="loc.account_title_fullscreen"><icon name="ico-fullscreen" class="btn-fullscreen"></icon></button>
			</div>
		</div>
		
		<!-- <div id="account_bottom" v-show="showBottom">
			<language-selector :languages="languages" :loc="loc" :selectedLanguageCode="selectedLanguageCode" :langOptions="currentLangOptions"></language-selector>
			<button id="signInButton" v-show="(isAnonymous && showSignIn)" @click="onSignInClicked" class="ss_button btn_yolk bevel_yolk">{{ loc.sign_in }}</button>
			<button id="signOutButton" v-show="!isAnonymous" @click="onSignOutClicked" class="ss_button btn_yolk bevel_yolk">{{ loc.sign_out }}</button>
			<div id="player_photo" class="box_relative" v-show="photoUrl !== null && photoUrl !== undefined && photoUrl !== '' && ! isAnonymous">
				<img :src="photoUrl" class="roundme_sm bevel_blue"/>
				<div v-if="isTwitch" class="box_aboslute account-panel-twitch roundme_sm" @click="onTwitchIconClick"><i class="fab fa-twitch"></i></div>
			</div>
		</div> -->
	</div>

</script>
<template id="egg-store-notify">
    <div v-if="show" class="egg-store-sale-notify" :class="{'white-blue' : color}" @click="notifyClick">
        <button class="account_icon roundme_sm account_icon-item" :title="getTitle"><i aria-hidden="true" class="fas" :class="icon"><span class="hideme">Egg</span></i>
           <span class="text" :class="{hideme : textHide}"> {{loc[text]}}</span>
        </button>
    </div>
</template>
<script>
    const compEggStoreSaleNotify = {
        template: '#egg-store-notify',
        props: ['loc', 'show', 'sku', 'textHide', 'text', 'icon', 'color', 'url', 'title', 'analytics'],
        methods: {
            notifyClick() {
                if (this.analytics) ga('send', 'event', 'header-buttons', 'click', this.analytics);

                if (this.url) {
                    window.open(this.url, '_window');
                    return;
                }

                // if (!vueData.firebaseId) {
				//     vueApp.showGenericPopup('p_redeem_error_no_player_title', 'p_redeem_error_no_player_content', 'ok');
				//     return;
                // }

                vueApp.eggStoreReferral = 'Sale notify ref';

                if (this.sku) {
                    return vueApp.showPopupEggStoreSingle(this.sku)
                }

                return vueApp.onPremiumItemsClicked();
            }
        },
        computed: {
            getTitle() {
                if (!this.title) return null;
                return this.loc[this.title];
            }
        }
    };
</script><script>
var comp_account_panel = {
	template: '#account-panel-template',
	components: {
		'language-selector': comp_language_selector,
		'eggstore-notify': compEggStoreSaleNotify,
		'event-panel': comp_events,
	},

	props: ['loc', 'eggs', 'languages', 'selectedLanguageCode', 'isPaused', 'photoUrl', 'isAnonymous', 'isOfAge', 'showTargetedAds', 'showCornerButtons', 'ui', 'isEggStoreSale', 'sku', 'isSubscriber', 'isTwitch', 'currentLangOptions', 'currentScreen', 'screens'],

	
	data: function () {
		return {
			languageCode: this.selectedLanguageCode,
			eggBalance: 0,
			vueData,
		}
	},

	created() {
		this.getEggsLocalStorage();
	},
	methods: {
		getEggsLocalStorage() {
			const raw = localStore.getItem('localLoadOut');
			if (!raw) {
				return;
			}
			const storage = JSON.parse(raw);
			if (!'balance' in storage) {
				return;
			}
			return this.eggBalance = storage.balance;

		},
		onEggStoreClick: function () {
			if (vueData.showAdBlockerVideoAd) {
				return;
			}
			if (!vueData.firebaseId) {
				vueApp.showGenericPopup('p_redeem_error_no_player_title', 'p_redeem_error_no_player_content', 'ok');
				return;
			}
			vueApp.switchToEquipUi(vueApp.equipMode.shop)
			BAWK.play('ui_popupopen');
			this.gaSend('shoppingCart');
		},
		itemStoreClick: function() {
			this.gaSend('openItemShop');
			vueApp.switchToEquipUi();
			vueApp.$refs.equipScreen.switchToShop();
			BAWK.play('ui_popupopen');
		},
		onHelpClick: function () {
			vueApp.showHelpPopup();
			this.gaSend('openHelp');
			BAWK.play('ui_popupopen');
		},

		onSettingsClick: function () {
			this.gaSend('openSettings');
			this.onSharedPopupOpen();
			vueApp.showSettingsPopup();
			BAWK.play('ui_popupopen');
		},

		onFullscreenClick: function () {
			extern.toggleFullscreen();
			BAWK.play('ui_click');
		},

		onSignInClicked: function () {
			vueApp.setDarkOverlay(true);
			this.$emit('sign-in-clicked');
		},

		onSignOutClicked: function () {
			vueApp.setDarkOverlay(true);
			this.$emit('sign-out-clicked');
		},

		onShareLinkClick: function () {
			this.gaSend('openShareLink');
			this.onSharedPopupOpen();
            extern.inviteFriends();
		},

		onAnonWarningClick: function() {
			ga('send', 'event', vueApp.googleAnalytics.cat.playerStats, vueApp.googleAnalytics.action.anonymousPopupOpen);
			vueApp.showAttentionPopup();
		},
		onSubscriptionClick() {
			this.gaSend('openVipPopup');
			vueApp.showSubStorePopup();
		},
		gaSend(label) {
			if (!label) return;
            ga('send', 'event', 'header-buttons', 'click', label);
		},
		onTwitchIconClick() {
			window.open(dynamicContentPrefix + 'twitch');
		},
		onAccountBtnClick() {
			if (this.isAnonymous && this.showSignIn) {
				this.onSignInClicked();
			} else {
				this.onSubscriptionClick();
			}
		},
		eggShake() {
			this.$refs.eggCounter.classList.add('egg-shake');
			setTimeout(() => this.$refs.eggCounter.classList.remove('egg-shake'), 300);
		},
		onSharedPopupOpen() {
			if (extern.inGame) {
				vueApp.hideRespawnDisplayAd();
			}
		},
		onSharedPopupClosed() {
			if (extern.inGame) {
				vueApp.showRespawnDisplayAd();
			}
		},
	},

	computed: {
		showSignIn: function () {
			if (!isFromEU) {
				return true;
			}

			return isFromEU && this.isOfAge && this.showTargetedAds;
		},

		showShareLinkButton: function () {
			return this.showCornerButtons && this.currentScreen === this.screens.game;
		},

		hideNewItemNotify() {
			// if (!ssChangelogDate) return false;
			// if (!this.showCornerButtons) return false;
			// const lapsed = Date.now() - ssChangelogDate.valueOf(),
			// 	  days = Math.floor((lapsed / (60*60*24*1000)));

			// if (days <= 5 ) return true;
			// return false;
			return;
		},

		vipButtonText() {
			return this.isSubscriber && !extern.account.upgradeIsExpired ? '' : this.loc.s_btn_txt_subscribe;
		},
		showVipButton() {
			return this.showScreen === this.screens.home || this.showScreen === this.screens.equip;
		},
		isVipLive() {
			return this.isSubscriber && !extern.account.upgradeIsExpired;
		},
		accountBtnCls() {
			if (this.isAnonymous && this.showSignIn) {
				return 'btn_green bevel_green'
			} else {
				if (this.isSubscriber) {
					return 'btn_yolk bevel_yolk btn_vip width-auto';
				} else {
					return 'btn_yolk bevel_yolk btn_vip';
				}
			}

		},
		accountBtnText() {
			if (this.isAnonymous && this.showSignIn) {
				return this.loc.sign_in;
			} else {
				if (this.isSubscriber) {
					return '';
				} else {
					return this.loc.s_btn_txt_subscribe;
				}
			}
		},
		isNotSignedIn() {
			return this.isAnonymous && this.showSignIn;
		},
		showNotInGame() {
			if (this.currentScreen !== this.screens.game && !extern.inGame) {
				return true;
			} else {
				return false;
			}
		}
	},
	watch: {
		eggs() {
			this.eggShake();
			this.eggBalance = this.eggs;
		}
	}
};
</script>
<script id="play-panel-template" type="text/x-template">
	<div id="play-panel" class="box_relative">
		<weapon-select-panel id="weapon_select" class="justify-content-center centered_x" :loc="loc" :account-settled="isGameReady" :current-class="currentClass" :current-screen="showScreen" :screens="screens" :play-clicked="playClicked" @changed-class="onChangedClass"></weapon-select-panel>
		<ss-button-dropdown class="play-panel-region-select" :loc="loc" :loc-txt="serverText"  :selected-item="currentRegionId" @onListItemClick="onRegionPicked" menuPos="bottom">
			<template slot="dropdown">
				<li v-if="regionList" ref="items" v-for="(g, idx) in regionList" :class="{ 'selected' : currentRegionId === g.id }" class="display-grid gap-sm align-items-center text_blue5 font-nunito regions-select" @click="onRegionPicked(g.id)">
					<div class="f_row align-items-center">
						<icon v-show="currentRegionId === g.id" name="ico-checkmark" class="option-box-checkmark"></icon>
					</div>
					<div>
						{{ loc[g.locKey ]}}
					</div>
					<div class="text-right">
						{{ g.ping }}ms
					</div>
				</li>
			</template>
		</ss-button-dropdown>
		<div class="play-panel-btn-group display-grid grid-auto-flow-column gap-1 centered_x">
			<button @click="onJoinPrivateGameClick" class="is-for-play ss_button btn_big btn_blue_light bevel_blue_light btn_play_w_friends display-grid align-items-center box_relative"><span>{{ loc.p_privatematch_friends }}</span></button>
			<button @click="onPlayButtonClick" class="is-for-play ss_button btn_big btn_yolk bevel_yolk play-button box_relative"><i class="fa fa-play fa-sm"></i> {{ loc.home_play }}</button>
			<ss-button-dropdown :loc="loc" :loc-txt="gameTypeTxt" :list-items="gameTypes" :selected-item="pickedGameType" @onListItemClick="onGameTypeChange" @dropdownOpen="onGameTypeBtnOpen" @dropdownClosed="onGameTypeBtnClosed"></ss-button-dropdown>
		</div>
		<!-- Popup: Pick Region -->
		<large-popup id="pickRegionPopup" ref="pickRegionPopup" @popup-closed="onPickRegionPopupClosed">
			<template slot="header">{{ loc.server }}</template>
			<template slot="content">
				<region-list-popup id="region_list_popup" ref="regionListPopup" v-if="(regionList.length > 0)" :loc="loc" :regions="regionList" :region-id="currentRegionId" @region-picked="onRegionPicked"></region-list-popup>
			</template>
		</large-popup>

		<!-- Popup: Join Private Game -->
		<large-popup id="joinPrivateGamePopup" ref="joinPrivateGamePopup" :popup-model="home.joinPrivateGamePopup" @popup-confirm="onJoinConfirmed" :hide-cancel="true">
			<template slot="content">
				<create-private-game-popup id="createPrivateGame" ref="createPrivateGame" :loc="loc" :region-loc-key="regionLocKey" :is-game-ready="isGameReady" :picked-game-type="pickedGameType" :game-type-txt="gameTypeTxt" :game-types="gameTypes" @onGameTypeChange="onGameTypeChange" @onRegionPicked="onRegionPicked" map-img-base-path="maps/" :mapList="maps" :regions="regionList" :currentRegionId="currentRegionId"></create-private-game-popup>
				<div class="error_text shadow_red" v-show="home.joinPrivateGamePopup.showInvalidCodeMsg">{{ loc.p_game_code_blank }}</div>
				<div class="private-game-wrapper fullwidth ss_margintop_lg">
					<div class="inner-wrapper">
						<header>
							<h1 class="nospace">{{ loc.p_game_code_title }}</h1>
						</header>
						<div class="display-grid grid-column-2-1 gap-sm">
							<input type="text" class="ss_field fullwidth" v-model="home.joinPrivateGamePopup.code" v-bind:placeholder="loc.p_game_code_enter" @focus="onJoinGameFocus" v-on:keyup.enter="onJoinConfirmed">
							<button class="ss_button common-box-shadow" @click="onJoinConfirmed">Join Game!</button>
						</div>
					</div>
				</div>
			</template>

			<template slot="cancel">{{ loc.cancel }}</template>
			<template slot="confirm">{{ loc.confirm }}</template>
		</large-popup>

		<small-popup id="showGameModePopup" ref="showGameModePopup" @popup-confirm="onGameModePopupConfirm">
			<template slot="header">Game Mode</template>
			<template slot="content">
			<div class="select-box-wrap">
				<label for="create-select-type" class="ss_button btn_yolk bevel_yolk"><i class="fas fa-chevron-down"></i></label>
				<select id="create-select-type" name="gameType" v-model="pickedGameType" class="ss_select select" @change="onGameTypeChange($event)">
					<option v-for="g in gameTypes" v-bind:value="g.value" :class="'game-select-' + g.locKey" v-html="loc[g.locKey]"></option>
				</select>
			</div>
			</template>
			<template slot="cancel">{{ loc.cancel }}</template>
			<template slot="confirm">{{ loc.confirm }}</template>
		</small-popup>
	</div>
</script>

<script id="region-list-template" type="text/x-template">
    <div>
        <h1 class="roundme_sm">{{ loc.p_servers_title }}</h1>
		{{ regions }} - {{ regionId }}
        <div v-for="r in regions" :key="r.id">
            <div id="region_list_item">
                <input type="radio" :id="('rb_' + r.id)" name="pickRegion" v-bind:value="r.id" v-model="regionId" @click="BAWK.play('ui_onchange')">
                <label :for="('rb_' + r.id)" class="regionName">{{ extern.getLocText(r.locKey) }} </label>
                <label :for="('rb_' + r.id)" class="regionPingWrap roundme_sm">
                    <span class="pingBar" :class="barColorClass(r)" :style="barStyle(r)"></span>
                </label>
                <label :for="('rb_' + r.id)" class="regionPingNumber ss_marginleft_lg"> {{ r.ping }}ms</label>
            </div>
        </div>
        <div id="btn_horizontal" class="f_center">
			<button @click="onConfirmClick()" class="ss_button btn_green bevel_green btn_sm">{{ loc.ok }}</button>
		</div>
    </div>
</script>

<script>
var comp_region_list_popup = {
    template: '#region-list-template',
    props: ['loc', 'regions', 'regionId'],

    data: function () {
        return {
            colorClasses: ['greenPing', 'yellowPing','orangePing', 'redPing'],
        }
    },

    methods: {
		playSound (sound) {
			BAWK.play(sound);
        },
        
        barColorClass: function (region) {
            var colorIdx = Math.min(3, Math.floor(region.ping / 150));
            return this.colorClasses[colorIdx];
        },

        barStyle: function (region) {
            return {
                width: (10 - Math.min(9, region.ping / 50)) + 'em'
            }
        },

        onConfirmClick: function () {
            this.$emit('region-picked', this.regionId);
            this.$parent.close();
            BAWK.play('ui_playconfirm');
        }
    }
};
</script><template id="weaponselect_panel_template" type="text/x-template">
	<div class="center_h ss_marginbottom_sm">
		<div v-if="currentScreen !== screens.game" class="grid-span-column-all text-center align-items-center ss_marginbottom_sm">
			<h3 class="nospace text_blue8">{{ weapon.title }}</h3>
			<p class="nospace text_blue3"><i>{{ weapon.desc }}</i></p>
		</div>
		<div class="display-grid grid-auto-flow-column justify-content-around gap-sm">
			<div class="nospace" @click="selectClass(charClass.Soldier)">
				<icon name="ico-weapon-soldier" class="weapon_img roundme_md" :cls="addSelectedCssClass(charClass.Soldier)"></icon>
			</div>
			<div class="nospace" @click="selectClass(charClass.Scrambler)">
				<icon name="ico-weapon-scrambler" class="weapon_img roundme_md" :cls="addSelectedCssClass(charClass.Scrambler)"></icon>
			</div>
			<div class="nospace" @click="selectClass(charClass.Ranger)">
				<icon name="ico-weapon-ranger" class="weapon_img roundme_md" :cls="addSelectedCssClass(charClass.Ranger)"></icon>
			</div>
			<div class="nospace" @click="selectClass(charClass.Eggsploder)">
				<icon name="ico-weapon-rpegg" class="weapon_img roundme_md" :cls="addSelectedCssClass(charClass.Eggsploder)"></icon>
			</div>
			<div class="nospace" @click="selectClass(charClass.Whipper)">
				<icon name="ico-weapon-whipper" class="weapon_img roundme_md" :cls="addSelectedCssClass(charClass.Whipper)"></icon>
			</div>
			<div class="nospace" @click="selectClass(charClass.Crackshot)">
				<icon name="ico-weapon-crackshot" class="weapon_img roundme_md" :cls="addSelectedCssClass(charClass.Crackshot)"></icon>
			</div>
			<div class="nospace" @click="selectClass(charClass.TriHard)">
				<icon name="ico-weapon-trihard" class="weapon_img roundme_md" :cls="addSelectedCssClass(charClass.TriHard)"></icon>
			</div>
		</div>
		<div v-if="currentScreen == screens.game" class="grid-span-column-all text-center align-items-center ss_marginbottom_sm">
			<h3 class="nospace text_blue8">{{ weapon.title }}</h3>
			<p class="nospace text_blue3"><i>{{ weapon.desc }}</i></p>
		</div>
	</div>
</template>

<script>
var comp_weapon_select_panel = {
	template: '#weaponselect_panel_template',
	props: ['currentClass', 'loc', 'accountSettled', 'playClicked', 'currentScreen', 'screens'],

	data: function () {
		return {
			charClass: CharClass,
		}
	},

	methods: {
		selectClass: function (classIdx) {
			if (!extern.inGame && (!this.accountSettled || this.playClicked || (this.currentClass === classIdx))) {
				return;
			} else {
				extern.changeClass(classIdx);
				this.$emit('changed-class', classIdx);
				BAWK.play('ui_click');
			}
		},

		addSelectedCssClass: function (classIdx) {
			if (!extern.inGame && this.playClicked) {
				return;
			} else {
				return (this.currentClass === classIdx)
				? 'weapon_selected'
				: '';
			}
		},
	},
	computed: {
		weapon() {
			let className = getKeyByValue(this.charClass, this.currentClass).toLowerCase();
			return {
				title: this.loc[`weapon_${className}_title`],
				desc: this.loc[`weapon_${className}_content`],
			}
		},
	}
};
</script>

<script>
var comp_play_panel = {
	template: '#play-panel-template',
	components: {
		'create-private-game-popup': comp_create_private_game_popup,
		'region-list-popup': comp_region_list_popup,
		'weapon-select-panel': comp_weapon_select_panel,
	},

	props: ['loc', 'playerName', 'gameTypes', 'currentGameType', 'regionList', 'currentRegionId', 'home', 'isGameReady', 'maps', 'currentClass', 'showScreen', 'screens', 'playClicked'],

	data: function() {
		return {
			pickedGameType: this.currentGameType,
			isButtonDisabled: true,
			playClickedBeforeReady: false,
			playClickFunction: Function,
			kotcPrompt: '',
			typeSelect: '',
			isPromptOpen: false
		}
	},
			
	methods: {
		onPickRegionButtonClick: function () {
			this.$refs.pickRegionPopup.toggle();
			BAWK.play('ui_popupopen');
		},
		
		onRegionPicked: function (regionId) {
			if (vueData.currentRegionId === regionId) { return; }
			
			vueData.currentRegionId = regionId;
			extern.selectRegion(vueData.currentRegionId);
			BAWK.play('ui_onchange');
		},

		onPickRegionPopupClosed: function () {
			if (this.$refs.createPrivateGame.showingRegionList) {
				this.$refs.createPrivateGame.showingRegionList = false;
				this.$refs.createPrivateGamePopup.toggle();
				this.$refs.createPrivateGame.onKeyDownMapSelect();
			}
		},

		onNameChange: function (event) {
			console.log('name changed to: ' + event.target.value);
			this.$emit('playerNameChanged', event.target.value);
		},

		onPlayerNameKeyUp: function (event) {
			event.target.value = extern.filterUnicode(event.target.value);
			event.target.value = extern.fixStringWidth(event.target.value);
			event.target.value = event.target.value.substring(0, 128);

			// Send username to server to start the game!
			if (event.code == "Enter" || event.keyCode == 13) {
				if (vueData.playerName.length > 0) {
					vueApp.externPlayObject(vueData.playTypes.joinPublic, this.pickedGameType, this.playerName, -1, '');
				}
			}
		},
		onGameTypeBtnOpen() {
			if (this.isWinSizeSmall()) vueApp.hideTitleScreenAd();
		},
		onGameTypeBtnClosed() {
			if (this.isWinSizeSmall()) vueApp.showTitleScreenAd();
		},
		isWinSizeSmall() {
			var win = window,
				doc = document,
				docElem = doc.documentElement,
				body = doc.getElementsByTagName('body')[0],
				x = win.innerWidth || docElem.clientWidth || body.clientWidth,
				y = win.innerHeight|| docElem.clientHeight|| body.clientHeight;
			// if (x <= 1366 && y <= 768) {
			if (x <= 1366) {
				return true;
			}
			return false;
		},

		onGameTypeChange: function (event) {
			let type;
			if (event.target !== undefined) {
				type = event.target.value;
			} else {
				type = event;
			}

			this.pickedGameType = type;
			this.$emit('game-type-changed', this.pickedGameType);
			extern.selectGameType(this.pickedGameType);
			BAWK.play('ui_onchange');
		},
		onPlayTypeWhenSignInComplete() {
			return this.playClickFunction();
		},
		onPlaySentBeforeSignIn(callback) {
			this.gameClickedBeforeReady = true;
			vueApp.showSpinner('signin_auth_title', 'signin_auth_msg');
			this.playClickFunction = callback;
		},
		hasValidPlayerNameCheck() {
			console.log('invalid player name');
			vueApp.showGenericPopup('play_pu_name_title', 'play_pu_name_content', 'ok');
			vueApp.hideSpinner();
			return;
		},
		onPlayButtonClick: function () {
			if (!hasValue(this.playerName)) {
				this.hasValidPlayerNameCheck();
				return;
			}
			if (!this.isGameReady) {
				this.onPlaySentBeforeSignIn(this.onPlayButtonClick);
				return;
			}

			this.onCreateGameClosed();
			vueApp.game.respawnTime = 0;
			vueApp.externPlayObject(vueData.playTypes.joinPublic, this.pickedGameType, this.playerName, -1, '');
			BAWK.play('ui_playconfirm');
		},

		onCreatePrivateGameClick: function () {
			this.$refs.createPrivateGamePopup.toggle();
			this.$refs.createPrivateGame.onKeyDownMapSelect();
			BAWK.play('ui_popupopen');
		},

		onCreateGameClosed() {
			this.$refs.createPrivateGame.removeKeydown();
		},

		onJoinPrivateGameClick: function () {
			this.showJoinPrivateGamePopup(vueData.home.joinPrivateGamePopup.code);
			BAWK.play('ui_popupopen');
		},

		showJoinPrivateGamePopup: function (showCode) {
			// The popup must be active before it will update; set code after showing
			this.$refs.joinPrivateGamePopup.show();
			vueData.home.joinPrivateGamePopup.code = showCode;
		},

		onJoinConfirmed: function () {
			if (!hasValue(this.playerName)) {
				this.hasValidPlayerNameCheck();
				return;
			}
			if (!this.isGameReady) {
				this.onPlaySentBeforeSignIn(this.onJoinConfirmed)
				return;
			}

			let match = null;

			if (vueData.home.joinPrivateGamePopup.code.match(/\#\w+/)) {
				match = vueData.home.joinPrivateGamePopup.code.match(/\#\w+/)[0];
			} else if (vueData.home.joinPrivateGamePopup.code.includes('crazyShare')) {
				match = vueData.home.joinPrivateGamePopup.code.match(/=\w*$/)[0].substring(1);
			}
			else { // In case someone copy/pastes the thing without including the #
				match = vueData.home.joinPrivateGamePopup.code;
			}

			if (!match) {
				return;
			}

            match = match.trim();
            if (match.startsWith('#')) match = match.substring(1)

			vueData.home.joinPrivateGamePopup.code = match;

			this.$refs.joinPrivateGamePopup.hide();

			// checking if the invite code is being used since, we are only trying to determine
			extern.onJoinGameClick = true;
			vueApp.externPlayObject(vueData.playTypes.joinPrivate, '', this.playerName, '', vueData.home.joinPrivateGamePopup.code);
		},
		kotcAttachSetup() {
			const typePostion = this.typeSelect.getBoundingClientRect();
			const kotcPrompt = this.kotcPrompt.getBoundingClientRect();

			this.kotcPrompt.style.top = typePostion.top + 'px';
			this.kotcPrompt.style.left = typePostion.right + 16 + 'px';
		},

		anchorKotcPrompt() {
			this.$nextTick(() => this.kotcAttachSetup());
		},
		onGameTypeClick() {
			if (this.isPromptOpen) {
				this.isPromptOpen = false;
			} else {
				this.isPromptOpen = true;
			}
			// this.$refs.showGameModePopup.show();
		},
		onGameModePopupConfirm() {
			this.$refs.showGameModePopup.hide();
		},

		onJoinGameFocus() {
			this.$refs.createPrivateGame.removeKeydown();
		},
		onChangedClass() {
			vueApp.$refs.equipScreen.onChangedClass();
		}
	},

	computed: {
		regionLocKey: function () {
			if (!hasValue(this.regionList) || this.regionList.length === 0) {
				return '';
			}

			var region = this.regionList.find(r => {
				return r.id == vueData.currentRegionId;
			});

			return hasValue(region) ? 'server_' + region.id : '';
		},
		regionName: function () {
			if (!hasValue(this.regionList) || this.regionList.length === 0) {
				return 'N/A';
			}

			var region = this.regionList.find(r => {
				return r.id == vueData.currentRegionId;
			});

			if (!region) return 'N/A';

			return this.loc[region.locKey] || region.id;
		},

		selectedGameType() {
			return this.loc[this.gameTypes.filter(el => el.value === this.pickedGameType)[0]['locKey']]; 
		},
		gameTypeTxt() {
			return {
				title: this.loc.stat_game_mode,
				subTitle: this.loc[this.gameTypes.filter(el => el.value === this.pickedGameType)[0]['locKey']]
			}
		},
		serverText () {
			return {
				title: this.loc.p_servers_title,
				subTitle: this.currentRegionId ? this.loc[`server_${this.currentRegionId}`] : '',
			}
		},

	},
	watch: {
		currentGameType: function (val) {
			this.pickedGameType = val;
		},
		isGameReady(val) {
			this.isButtonDisabled = val ? false : true;
			if (this.gameClickedBeforeReady && val) {
				this.onPlayTypeWhenSignInComplete()
			}
		}
	}
};
</script><script id="newsfeed-panel-template" type="text/x-template">
	<section class="news-panel v_scroll">
		<article v-if="items" v-for="item in activeItems" :key="item.id" @click="onItemThatIsClicked(item)" class="media-item news_item clickme">
			<img :src="imageSrc(item)" class="news_img roundme_sm">
			<p>{{ item.content }}</p>
		</article>
	</section>
</script>

<script>
var comp_newsfeed_panel = {
	template: '#newsfeed-panel-template',
	props: ['items'],

	data: function () {
		return vueData;
	},

	// mounted: function () {
	// 	// this.fetchWebData();
	// 	this.checklocalForNewsData();
	// },

	methods: {
		imageSrc(item) {
			return dynamicContentPrefix + 'data/img/newsItems/' + item.id + item.imageExt;
		},
		onItemThatIsClicked(item) {
			console.log(item);
			extern.clickedWebFeedItem(item);
			BAWK.play('ui_click');
		},
	},
	computed: {
		activeItems() {
			return this.items.filter(item => item.active);
		}
	}
};
</script><script id="chicken-panel-template" type="text/x-template">
	<div id="showBuyPassDialogButton" class="new">
		<div class="chicken-panel--upgraded" v-show="doUpgraded && !isSubscriber">
			<div class="tool-tip tool-tip--right">
				<span v-if="nugCounter" id="nugget-countdown">{{nugCounter}} Minutes remaining.</span>
				<img class="upgraded-nugget" src="img/chicken-nugget/goldenNugget_static.png">
				<!-- <div id="nugget-timer" class="nugget-timer--wrapper">
					<div class="timer-background"></div>
					<div class="timer spinner"></div>
					<div class="timer filler"></div>
					<div class="mask"></div>
				</div> -->
			</div>
		</div>
		
		<div class="chicken-panel--no-upgraded" v-show="!doUpgraded">
			<img src="img/chicken-nugget/starburst.png" @click="onChickenClick" class="clickme starburst">
			<img src="img/chicken-nugget/goldenNuggetGIFWIP.gif" @click="onChickenClick" class="clickme nugget-chick">
		
			<div id="buyPassChickenSpeech">
				<img src="img/speechtail.png" class="buyPassChickenSpeechTail">
				<span v-html="loc.chicken_cta"></span>
			</div>
		</div>
	</div>
</script>

<script>
var comp_chicken_panel = {
	template: '#chicken-panel-template',
	props: ['local', 'doUpgraded'],
	data: function () {
		return vueData;
    },
	methods: {
		onChickenClick: function () {
			BAWK.play('ui_chicken');
			vueApp.showGoldChickenPopup();
			ga('send', 'event', this.googleAnalytics.cat.purchases, 'Golden Chicken Click');
		},
	},
};
</script><script id="footer-links-panel-template" type="text/x-template">
	<footer class="main-footer">
		<!-- <section class="social-icons">
			<social-panel id="social_panel" :loc="loc" :is-poki="isPoki" :use-social="selectedSocial" :social-media="socialMedia"></social-panel>
		</section> -->
		<section class="centered">
			<nav class="footer-nav text-center">
				<button @click="onChangelogClicked" class="clickme ss_button_as_text">{{ version }}</button> | 
				<!-- <a href="https://shell-shockers.myshopify.com/collections/all" target="_blank" @click="BAWK.play('ui_click')">{{ loc.footer_merchandise }}</a> |  -->
				<button class="ss_button_as_text" target="_blank" @click="openInNewTab('https://www.bluewizard.com/privacypolicy')">{{ loc.footer_privacypolicy }}</button> | 
				<button class="ss_button_as_text" target="_blank" @click="openInNewTab('https://bluewizard.com/terms/')">{{ loc.footer_termsofservice }}</button> | 
				<button class="ss_button_as_text" @click="onHelpClick">{{ loc['account_title_faq'] }}</button> | 
				<button class="ss_button_as_text" target="_blank" @click="openInNewTab('https://www.bluewizard.com')">&copy; 2023 <img class="main-footer--logo-blue-wiz-mini" src="img/blue-wizard-logo-tiny-min.png" :alt="loc.footer_bluewizard + ' logo'"><span class="hideme">{{ loc.footer_bluewizard }}</span></button>
			</nav>
		</section>
	</footer>
</script>

<script>
var comp_footer_links_panel = {
	template: '#footer-links-panel-template',
	props: ['loc', 'version', 'isPoki', 'socialMedia', 'selectedSocial'],

	methods: {
		onChangelogClicked: function () {
			vueApp.showChangelogPopup();
			BAWK.play('ui_popupopen');
		},
		playSound() {
			BAWK.play('ui_click');
		},
		onHelpClick() {
			vueApp.showHelpPopup();
			// this.gaSend('openHelp');
			BAWK.play('ui_popupopen');
		},
		openInNewTab(url) {
			window.open(url, '_blank').focus();
			this.playSound();
		}
	}
};
</script>
<template id="comp-vip-cta">
    <div class="vip-club-cta">
        <button v-if="!isSubscriber && hasMobileReward && showVip" class="vip-club-cta-pos ss_button btn_sm btn_pink bevel_pink" @click="onClicked">
            {{ loc.ui_game_playeractions_join_vip }}
        </button>
        <h4 v-if="isSubscriber && showVip" class="sub-name">
            {{loc[subName]}}
        </h4>
	<img :class="imgCls" :src="getImgSrc" :alt="getImgSrcAlt" @click="onClicked">
    </div>
</template>

<script>
    const CompHouseAd = {
        template: '#comp-vip-cta',
        props: ['loc', 'upgradeName', 'isUpgraded', 'isSubscriber', 'hasMobileReward', 'isPoki', 'eventData', 'chwCount', 'chwReady', 'chwLimitReached'],
        data() {
            return {
                subName: '',
				hasPlayedKotc: null,
				random: ['twitch-drops']
            };
        },
		mounted() {
			this.hasPlayedKotc = localStore.getItem('hasPlayedKotc');
		},
        methods: {
            onClicked() {
				switch (this.useEventData) {
					case 'twitch-drops':
						window.open(dynamicContentPrefix + 'twitch?utm_medium=referral&utm_campaign=featureslot', '_blank');
						break;

					case 'egg-org':
						vueApp.showSelectedTaggedItemsOnEquipScreen('EGGORG');
						break;
					case 'black-fryday':
						vueApp.showEggStorePopup();
						break;

					case 'kotcPopup':
						vueApp.showKotcInstrucPopup();
						break;

					case 'scavengerHunt':
						vueApp.showScavengerHuntPopup();
						break;

					case 'chicknWinner':
						this.$emit('chw-video-request');
						break;

					case 'badEgg':
						if (this.eventData.event[this.useEventData].url) window.open(this.eventData.event[this.useEventData].url, '_blank');
						break;

					case 'mobile':
						ga('send', 'event', 'home-display-ad', 'click', 'mobile-ad');
						vueApp.showGetMobilePopup();
						break;
			
					default:
						if (!this.isPoki) {
							// if has mobile item
							if (this.hasMobileReward) {
								let vipType = 'vip-ad';
								if (this.isUpgraded && this.sSubscriber) {
									let vipType = 'vip-manage';
								}
								ga('send', 'event', 'home-display-ad', 'click', vipType);
								vueApp.showSubStorePopup();
								return;
							} else {
								ga('send', 'event', 'home-display-ad', 'click', 'mobile-ad');
								vueApp.showGetMobilePopup();
								return;
							}
						}
						break;
				}

				if (this.eventData.current) {
					ga('send', 'event', 'home-display-ad', 'click', this.useEventData);
				}

            },
        },
		computed: {
			getImgSrc() {
				if (this.useEventData) {
					return this.eventData.event[this.useEventData].img;
				} else {
					if (!this.isPoki) {
						if (this.hasMobileReward) {
							return this.eventData.event.vipImgSrc.img;
						} else {
							return this.eventData.event.mobile.img;
						}
					}
				}
			},

			getImgSrcAlt() {
				if (this.useEventData) {
					return this.eventData.event[this.useEventData].alt;
				} else {
					if ( !this.isPoki) {
							if (this.hasMobileReward) {
								return this.eventData.event.vipImgSrc.alt;
							} else {
								return this.eventData.event.mobile.alt;
							}
						}
				}
			},
			showVip() {
				return false;
				if (!this.hasPlayedKotc || !this.hasMobileReward || this.isBlackFryday) {
					return false;
				} else {
					return true;
				}
			},
			imgCls() {
				return `${this.useEventData}-img`;
			},
			useEventData() {
				if (this.random.length > 0) {
					return this.random[Math.floor(Math.random() * this.random.length)];
				} else {
					return this.eventData.current;
				}
			}

		},
        watch: {
            upgradeName(val) {
                if (!hasValue(val)) {
                    return;
                }
                this.subName = `s-${val.replace(' ', '-').toLowerCase().replace(' ', '-')}-title`;
            }
        }
    };
</script><script id="media-tabs-template" type="text/x-template">
  <div class="media-tabs-wrapper box_relative border-blue5 roundme_sm bg_blue6 common-box-shadow">
	<div class="media-tab-container display-grid align-items-center gap-sm bg_blue3">
		<h4 class="common-box-shadow text-shadow-black-40 text_white">{{ tabName }}</h4>
        <button id="news-tab" @click="selectTab" class="media-tab ss_smtab ss_marginright roundme_sm" :class="(showNewsTab ? 'selected' : '')"><i class="fas fa-bullhorn"></i></button>
        <button id="twitch-tab" @click="selectTab" class="media-tab ss_smtab ss_marginright roundme_sm" :class="(showTwitchTab ? 'selected' : '')"><i class="fab fa-twitch"></i></button>
        <button id="video-tab" v-if="youtubeStreams.length > 0" @click="selectTab" class="media-tab ss_smtab roundme_sm" :class="(showVideoTab ? 'selected' : '')"><i class="fab fa-youtube"></i></button>
    </div>
	<div v-show="showTwitchTab || showVideoTab" class="ss_margintop f_row text-center justify-content-center">
		<button id="tab-twitch-btn" @click="onApplyNowClick" class="ss_button btn_sm btn_yolk bevel_yolk">{{loc.home_media_apply_now}}</button>
	</div>
    <div class="media-tabs-content f_col" :class="{'tab-news-active' : showNewsTab}">
		<div class="tab-content ss_paddingright ss_paddingleft">
			<div class="news-container f_row ss_margintop">
                <newsfeed-panel v-show="showNewsTab" id="news_scroll" class="media-tab-scroll" ref="newsScroll" :items="newsfeedItems"></newsfeed-panel>
                <streamer-panel v-show="showTwitchTab" id="twitch_panel" :loc="loc" :streams="twitchStreams" :title="loc.twitch_title" :viewers="loc.twitch_viewers" icon="ico_twitch"></streamer-panel>
                <section v-show="showVideoTab" id="yTube-scroll" class="media-tab-scroll v_scroll">
                    <article v-for="item in youtubeStreams" :key="item.id" v-if="item.active" @click="onVideoClick(item)" class="media-item ytube-item clickme">
						<div class="image-wrap news_img roundme_sm">
                            <img :src="item.externalImg" alt="" class="news_img" />
                        </div>
                        <div class="content-wrap f_col f_space_between">
                            <p>{{ item.title }}</p>
                            <p v-if="item.desc">{{ item.desc }}</p>
                            <p class="text-right">{{ item.author }}</p>
                        </div>
                    </article>
				</section>
			</div>
		</div>
        <!-- #news-tab -->
	</div>
    <!-- .media-tabs-content -->
  </div>
  <!-- .media-tabs-container -->
</script>

<script id="streamer-panel-template" type="text/x-template">
	<div class="panel_streamer noscroll">
		<!-- <div v-if="show" id="stream_mask"></div> -->
		<div id="stream_scroll" class="media-tab-scroll v_scroll" v-show="show">
			<div class="media-item stream_item clickme" v-for="s in streams">
				<a :href="s.link" target="_blank" @click="BAWK.play('ui_click')" class="display-grid grid-column-1-2">
					<img :src="s.image" class="stream_img roundme_sm">
					<span>
						<p class="stream_name">{{ s.name }}</p>
						<p class="stream_viewers">{{ s.viewers }} {{ viewers }}</p>
					</span>
				</a>
			</div>
		</div>
		<div class="no-stream roundme_sm" v-if="!show">
		 <p v-html="loc.twitch_no_steam"></p>
		</div>
	</div>
</script>

<script>
var comp_streamer_panel = {
	template: '#streamer-panel-template',
	props: ['streams', 'title', 'viewers', 'icon', 'loc'],
	methods: {
		playSound (sound) {
			BAWK.play(sound);
		}
	},
	computed: {
		show: function() {
			if (!this.streams) {
				return false;
			}

			return this.streams.length > 0;
		}
	}
};
</script>
<script>
    const MEDIATABS = {
        template: '#media-tabs-template',
        components: {
            'newsfeed-panel': comp_newsfeed_panel,
            'streamer-panel': comp_streamer_panel,
        },
        props: ['loc', 'newsfeedItems', 'twitchStreams', 'youtubeStreams'],

        created() {
            this.$nextTick(() => {
                this.randomTabSelect();
            });
        },

        mounted() {
            // VUE!!!!!!!!!!!
            setTimeout(() => this.getAllTabs(), 1000);
        },

        data: function () {
            return {
                mediaTabs: [],
                mediaTabsCount: 0,
                rotateTimeout: '',
                showNewsTab: false,
                showTwitchTab: false,
                showVideoTab: false,
                currentTab: '',
				tabContent: [],
            }
        },
        
        methods: {
            getAllTabs() {
                this.$nextTick(() => {
                    let ids = [],
                        tabs = Array.from(document.querySelectorAll('.media-tab')).forEach(tab => ids.push(tab.id));
                    this.mediaTabs = ids;
                    this.mediaTabsCount = (this.mediaTabs.length) - 1;
                    this.autoRotateTabs();
                });
            },

            selectTab: function (e) {
                ga('send', 'event', 'media-tabs', 'click', e.currentTarget.id);
                return this.switchTab(e.currentTarget.id, true, true)
            },

            autoRotateTabs() {
                let nextIdx = this.mediaTabs.indexOf(this.currentTab) + 1;
                if (nextIdx > this.mediaTabsCount) nextIdx = 0;

                this.rotateTimeout = setTimeout(() => {
                    this.switchTab(this.mediaTabs[nextIdx], false, false);
                    this.autoRotateTabs();
                }, 10000);
            },

            randomTabSelect() {
                let n = Math.floor(Math.random() * (3 - 1 + 1) + 1);
                if (n === 1) {
                    this.showNewsTab = true;
                    this.currentTab = 'news-tab';

                } else if (n >= 3 && n <= 5) {
                    this.showTwitchTab = true;
                    this.currentTab = 'twitch-tab';
                } else {
					this.currentTab = 'video-tab';
                    this.showVideoTab = true;
                }
            },

            onVideoClick(item) {
                ga('send', 'creator', 'videoClick', item.title);
                window.open(item.link, '_window');
            },

            onApplyNowClick(e) {
                ga('send', 'creator', 'applyNow', e.target.id);
                window.open('https://shellcreators.bluewizard.com', '_window');
            },
            
            switchTab(tab, sound, click) {
                this.showNewsTab = false;
                this.showTwitchTab = false;
                this.showVideoTab = false;

                switch (tab) {
                    case 'news-tab':
                        this.currentTab = 'news-tab';
                        this.showNewsTab = true;
                        break;

                    case 'twitch-tab':
                        this.currentTab = 'twitch-tab';
                        this.showTwitchTab = true;
                        break;

                    case 'video-tab':
                        this.currentTab = 'video-tab';
                        this.showVideoTab = true;
                        break;
                }

                if (sound) {
                    BAWK.play('ui_toggletab');
                }

                if (click) {
                    ga('send', 'creator', 'tabClick', this.currentTab);
                    this.cancelRotate(false);
                }
            },

            cancelRotate(stop) {
                clearTimeout(this.rotateTimeout);
                this.rotateTimeout = '';
                if (!stop) {
                    this.autoRotateTabs();
                }
            }
        },
		computed: {
			tabName() {
				if (this.showNewsTab) {
					return this.loc.home_latestnews;
				}

				if (this.showTwitchTab) {
					return 'Twitch'
				}

				if (this.showVideoTab) {
					return 'YouTube'
				}
			}
		}
    };
</script><template id="music-widget">
    <div v-if="isMusic" class="music-widget roundme_md" :class="[!show ? hideClass : '']">
        <div v-if="theAudio" class="music-widget--wrapper flex flex-nowrap">
            <figure class="music-widget--content">
                <header class=" roundme_md">
                    <h3 class="music-widget--now-playing">{{ loc.musicwidget_now_playing }}</h3>
                </header>

                <figcaption class="music-widget--content-wrapper">
                    <h4 class="music-widget--album-title">{{ serverTracks.artist }}</h4>
                </figcaption>
                <figcaption class="music-widget--content-wrapper">
                    <p v-if="serverTracks.url" class="music-widget--song-title"><a @click="gaSendEvent('click-track', getTitleAlbum)" :href="serverTracks.url" :title="theTitleAttr" target="_blank">{{ getTitleAlbum }}</a></p>
                    <p v-else class="music-widget--song-title">{{ getTitleAlbum }}</p>
                </figcaption>
                <div v-if="volumeSlider" v-for="t in settingsUi.adjusters.music" class="music-widget--volume-control nowrap">
                    <slider-component :loc="loc" :loc-key="t.locKey" :control-id="t.id" :control-value="t.value" :min="t.min" :max="t.max" :step="t.step" :multiplier="t.multiplier" @setting-adjusted="volumeControl"></slider-component>
                </div>
            </figure>
            <div class="music-widget--cover-image music-widget--cover-controls roundme_md">
                <template v-if="serverTracks.url">
                    <a v-if="serverTracks.albumArt" @click="gaSendEvent('click-albumArt', getTitleAlbum)" :href="serverTracks.url" :title="theTitleAttr" class="ss-absolute" target="_blank"><img :src="serverTracks.albumArt" class="roundme_md ss-absolute" :alt="serverTracks.album" /></a>
                </template>
                <template v-else>
                    <img v-if="serverTracks.albumArt" :src="serverTracks.albumArt" class="ss-absolute roundme_md" alt="Album cover art for" :alt="serverTracks.album" />
                </template>
                <button v-if="playBtn" @click="playAudio">
                    <i v-if="playing"  class="music-widget--cover-control-icon music-widget--cover-control-pause far fa-pause-circle fa-2x"></i>
                    <i v-if="!playing" class="music-widget--cover-control-icon music-widget--cover-control-pause far fa-play-circle fa-2x"></i>
                </button>
            </div>
            <div class="music-widget--sponsor">
                <a @click="gaSendEvent('click-sponsor', sponsor.name)" v-if="sponsor" :href="sponsor.link" :title="getSponsorTitleAttr" target="_blank"><img :src="getSponsorImg" class="music-widget--sponsor-icon" :alt="sponsor.name" /><span class="hideme">{{sponsor.name}}</span></a>
                <button v-if="settings" @click="openSettings" class="music-widget--sponsor--settings-btn"><i class="fas fa-cog"></i><span class="hideme">Music Settings</span></button>
                <button class="music-widget--sponsor--settings-btn" @click="toggleMusic"><i class="fas" aria-hidden="true" :class="togglePlayStopIcon"></i><span class="hideme">Music</span></button>
            </div>
        </div>
    </div>
</template>


<script>

    const createMusicWidget = templateId => {

        return {
            template: '#music-widget',
            props: ['loc','volumeSlider', 'playBtn', 'settings', 'settingsUi', 'show'],
            components: {
                'slider-component': comp_settings_adjuster
            },

            data () {
                return vueData.music;
            },

            mounted () {
                this.theAudio = document.getElementById('theAudio');
            },

            methods: {

                loadVolume() {
                    this.$nextTick(() => {
                        this.theAudio.volume = Number(this.settingsUi.adjusters.music[0].value);
                    });
                },
                getAudioServer() {
                    if (parsedUrl.dom == 'localhost' || parsedUrl.dom == 'dev' || parsedUrl.dom == 'localshelldev') {
                        var url = 'uswest2-music.shellshock.io';
                    }
                    else {
                        var server = vueApp.regionList.filter(server => region.locKey === vueApp.currentRegionLocKey)[0];
                        var url = server.subdom.slice(0, -1) + '-music.' + parsedUrl.dom + '.' + parsedUrl.top;
                    }

                    this.musicSrc = 'https://' + url + '/shellshock.ogg';
                },
                setIndex(k) {
                    this.$nextTick(() => {
                        this.play();
                    })
                    this.currIndex = k;
                },

                play() {
                    if (!this.isMusic) return;
                    this.getAudioServer();
                },

                playMusic() {
                    // var audio = this.theAudio;
                    this.theAudio.src = this.musicSrc;

                    console.log('Play Music');

                    clearInterval(this.timer);

                    this.theAudio.play()
                        .then( // Returns a Promise
                            () => { // Success
                                this.playing = true;
                                this.loadVolume();
                                this.duration = this.theAudio.duration;
                                this.theAudio.addEventListener('stalled', () => this.isMusic = false);
                                
                            },
                            () => { // Fail
                                // What to do... just try again after a few seconds, I guess?
                                setTimeout(() => this.play(), 2000);
                            }
                        );
                },

                pause() {
                    this.playing = false;
                    this.theAudio.pause();
                    clearInterval(this.timer);
                },
                /*
                next() {
                    if (this.currIndex < this.tracks.length - 1) {
                        this.currIndex++;
                    } else {
                        this.currIndex = 0;
                    }
                },

                prev() {
                    if (this.currIndex > 0) {
                        this.currIndex--;
                    } else {
                        this.currIndex = this.tracks.length - 1;
                    }
                },
                */
                playOnce() {
                    if (this.playing) return;
                    return this.play();
                },

                playAudio() {
                    if (this.theAudio.paused) {
                        this.play();
                    } else {
                        this.pause();
                    }
                },

                openSettings() {
                    vueApp.showSettingsPopup();
                    vueApp.onSettingsPopupSwitchTabMisc();
                    this.gaSendEvent('click', 'widgetOpenSettings');
                },

                volumeControl(id, value) {
                    extern.setMusicVolume(value);
                    vueApp.onSettingsQuickSave();
                },

                hideMe() {
                    return this.$refs.id.classList.addClass('fade-out-3');
                },
                showMe() {
                    this.show = true;
                    setTimeout(() => this.show = false, 2000);
                },
                gaSendEvent(action, label) {
                    action = action || '';
                    label = label || '';
                    return ga('send', 'event', 'music', action, label);
                },
                toggleMusic() {
                    if (this.playing) {
                        this.theAudio.removeEventListener('stalled', () => this.isMusic = false);
                        this.playing = false;
                        this.pause();
                        this.musicSrc = '';
                        this.theAudio.removeAttribute('src');
                        extern.setMusicStatus(false);
                        this.gaSendEvent('toggleMusic', 'off');
                        return;
                    }
                    this.theAudio.src = '';
                    this.play();
                    extern.setMusicStatus(true);
                    this.gaSendEvent('toggleMusic', 'on');
                },
                changeVolume(val) {
                    this.theAudio.volume = val;
                }
            },

            watch: {
                'currIndex': {
                    handler() {
                        this.$nextTick(() => {
                            this.play();
                        })
                    }
                },
                serverTracks(val) {
                    let sponsor = this.sponsors.filter(sponsor => sponsor.id === val.sponsor);
                    this.sponsor = sponsor.length || sponsor.length > 0 ? sponsor[0] : '';
				},
                musicSrc(val) {
                    if (val) {
                        this.playMusic();
                    }
                },
            },
            computed: {
                theTitleAttr() {
                    return 'Read more about ' + this.serverTracks.title;
                },
                getTitleAlbum() {
                    return this.serverTracks.title + ' - ' + this.serverTracks.album;
                },
                getSponsorImg() {
                    if (!this.sponsor) {
                        return;
                    }
                    return 'data/img/sponsor/' + this.sponsor.id + this.sponsor.imageExt;
                },
                getSponsorTitleAttr() {
                    return 'See more about our sponsor ' + this.sponsor.name;
                },
                togglePlayStopIcon() {
                    return this.playing ? 'fa-stop-circle' : 'fa-play-circle';
                },
            },
        };
    };
    // Register component globally
    Vue.component('music-widget', createMusicWidget('#music-widget'));
</script><script id="main-sidebar-template" type="text/x-template">
	<div id="screens-menu" v-if="isGamePaused" class="screens-menu box_relative">
		<!-- logo -->
		<div id="logo" class="box_relative">
			<a href="https://www.shellshock.io" @click="onLogoClick"><img class="home-screen-logo" src="img/logo.svg" @click="onLogoClick"></a>
			<!-- <img v-if="eggOrg" class="egg-org-logo" src="img/egg-org/logo_EggOrg.svg"> -->
			<button v-if="inGame && currentScreen !== screens.game" class="ss_button btn_md btn_green bevel_green box_relative box_absolute screens-menu-btn-return f_row align-items-center gap-sm" @click="onBackClick">{{ loc.p_pause_game_on }} <icon class="fill-white shadow-filter" name="ico-backToGame"></icon></button>
		</div>
		<div class="box_relative">
			<player-name v-if="currentScreen === screens.home" :loc="loc" :player-name="playerName" :picked-game-type="pickedGameType" :current-screen="currentScreen" :screens="screens"></player-name>
			<!-- main-menu -->
			<div id="main-menu" class="main-menu box_relative" :class="{'is-home-screen' : currentScreen === screens.home}">
				<menu class="nospace">
					<ul class="nospace text-left list-no-style display-grid gap-sm">
						<menu-item v-for="(item, idx) in menuItems" :loc="loc" :key="idx" :item="item" :current-screen="currentScreen" :screens="screens" :mode="mode" :currentMode="currentMode" :is-paused="inGame"></menu-item>
					</ul>
				</menu>
			</div>
		</div>
	</div>
	<!-- main-sidebar -->
</script>

<script id="main-menu-item" type="text/x-template">
		<li :class="listCls"><button class="fullwidth main-menu-button text-left font-sigmar roundme_sm text-uppercase box_relative f_row align-items-center" @click="onMenuItemClick" :class="itemCls">
			<svg class="centered" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 356.331 71"><path d="M293.124 71H4V9h340.08c11.045 0 16.429 13.48 8.42 21.084L321.213 59.79A40.795 40.795 0 0 1 293.123 71Z" style="fill-rule:evenodd;fill:#1192bc;opacity:.5"/><path class="main-nav-item-bg" d="M289.124 64H2V2h338.08c11.045 0 16.429 13.48 8.42 21.084L317.213 52.79A40.795 40.795 0 0 1 289.123 64Z" style="stroke-miterlimit:10;stroke-width:4px;fill-rule:evenodd"/></svg>
			<icon :name="getIcon" class="menu-icon"></icon>
			{{ loc[locKey] }} <i v-if="item.screen === screens.limited" class="fas fa-star box_absolute text_yellow_bright text-shadow-outline-blue"></i>
		</button></li>
</script>

<script id="chw-bubble-template" type="text/x-template">
	<div ref="chw-home-timer" v-show="!isPoki && firebaseId" class="chw-home-timer display-grid grid-column-1-2 align-items-center box_absolute gap-1 " :class="chwHomeTimerCls">
		<div>
			<img class="chw-home-timer-chick" :src="chwChickSrc">
		</div>
		<div class="display-grid align-items-center bg_white chw-circular-timer-container box_relative" :class="chwClass">
			<div v-show="chwShowTimer" class="chw-home-screen-timer"></div>
			<!-- #chw-circular-timer-outer -->
			<div>
				<p class="chw-circular-timer-countdown nospace">
					<span class="chw-pie-remaining text-center chw-msg chw-r-msg">{{ remainingMsg }}</span>
					<span v-show="chwShowTimer" class="chw-pie-num chw-pie-mins"></span><span v-show="chwShowTimer" class="chw-pie-num chw-pie-secs"></span>
				</p>
				<button v-if="ready && !hasChwPlayClicked && !error" class="ss_button btn_sm btn_yolk bevel_yolk" @click="playIncentivizedAd">{{ playAdText }}</button>
			</div>
		</div>
	</div>
	<!-- .chw-home-timer -->
</script>

<script>
var COMPCHWBUBBLE = {
	template: '#chw-bubble-template',
	components: {
	},

	props: ['loc', 'isPoki', 'firebaseId', 'limitReached', 'ready', 'error', 'counter', 'hasChwPlayClicked'],
	data: function() {
		return {
		}
	},		
	methods: {
		showNuggyPopup() {
			vueApp.showChicknWinnerPopup();
		},
		playIncentivizedAd(e) {
			if (this.showAdBlockerVideoAd) {
				return;
			}
			if (!this.ready || this.hasChwPlayClicked) {
				e.preventDefault();
				return;
			}
			ga('send', 'event', 'Chickn Winner', 'Free eggs btn', 'click-home');

			this.hasChwPlayClicked = true;
			vueApp.loadNuggetVideo();
			vueApp.chicknWinnerNotReady();
		},
		// needs emit
		chwShowCycle() {
			this.chwHomeEl = document.querySelector('.chw-home-timer');
			if (this.chwHomeEl) {
			this.chwHomeTimer = setInterval(() => {
				this.chwHomeEl.classList.toggle('active');
				}, this.chwActiveTimer);
			}
		},
	},
	computed: {
		playAdText() {
			if (this.ready && this.counter === 0) {
				return this.loc.chw_btn_free_reward;
			} else {
				return this.loc.chw_btn_free_reward;
			}
		},
		chwClass() {
			if (this.limitReached || this.error) {
				return 'grid-column-1-eq';
			} else {
				if (this.ready) {
					return 'grid-column-1-eq';
				} else {
					return 'grid-column-1-2';
				}
			}
		},
		chwHomeTimerCls() {
			//{'chw-home-screen-max-watched': limitReached}
			if (this.limitReached) {
				return 'chw-home-screen-max-watched';
			} else {
				if (this.ready) {
					return 'is-ready active';
				} else {
					return 'not-ready';
				}
			}
		},
		chwChickSrc() {
			if (this.limitReached || this.error) {
				return 'img/chicken-nugget/chickLoop_daily_limit.svg';
			} else {
				if (!this.ready) {
					return 'img/chicken-nugget/chickLoop_sleep.svg';
				} else {
					return 'img/chicken-nugget/chickLoop_speak.svg';
				}
			}
		},
		chwShowTimer() {
			if (this.limitReached) {
				// this.chwStopCycle();
				return false;
			} else {
				if (this.ready) {
					this.chwShowCycle();
					return false;
				} else {
					// this.chwStopCycle();
					return true;
				}
			}
		},
		remainingMsg() {
			if (this.error) {
				return this.loc.chw_error_text;
			}
			if (this.limitReached && this.counter > 0) {
				return this.loc.chw_daily_limit_msg;
			}
			if (this.ready) {
				if (this.counter === 0) {
					return this.loc.chw_ready_msg;
				} else {
					return this.loc.chw_cooldown_msg;
				}
			} else {
				return this.loc.chw_time_until;
			}
		},
		progressBarWrapClass() {
			if (this.ready) {
				return 'chw-progress-bar-wrap-complete';
			}
		},
	},
	watch: {
	}
};
</script>
<script>
const MainMenuItem = {
	template: '#main-menu-item',
	props: ['loc', 'item', 'currentScreen', 'screens', 'mode', 'currentMode', 'isPaused'],
	data: function() {
		return {
			inGame: false
		}
	},
	methods: {
		onMenuItemClick() {
			if (this.item.screen === this.currentScreen && !this.item.mode) {
				return;
			}
			switch (this.item.screen) {
				case this.screens.home:
					if (!extern.inGame) {
						vueApp.switchToHomeUi();
						if (this.currentScreen === this.screens.equip) {
							vueApp.onBackClick();
						}
					} else {
						vueApp.onHomeClicked();
						vueApp.showRespawnDisplayAd();
					}
					break;
				case this.screens.equip:
					if (this.item.mode.includes(this.mode.inventory)) {
						vueApp.openEquipUISwitchToInventory();
					} else {
						vueApp.openEquipUISwitchToShop();
					}
					if (extern.inGame) {
						setTimeout(() => {
							extern.resize();
						}, 200);
					}
					break;
				case this.screens.profile:
					if (this.currentScreen === this.screens.equip) {
						vueApp.onBackClick();
					}
					vueApp.switchToProfileUi();
					break;
				default:
					break;
			}
		},
		// showOn(item) {
		// 	if (item.locKey === 'account_title_home' && extern.inGame) {
		// 		return false;
		// 	} else {
		// 		return item.showOn.alwasyOn || (!item.showOn.alwasyOn && item.showOn.screen === this.currentScreen);
		// 	}
		// }
	},
	computed: {
		locKey() {
			if ((this.isPaused) && this.item.locKey === 'account_title_home') {
				return 'p_pause_quit';
			} else {
				return this.item.locKey;
			}
		},
		iconCls() {
			return `${this.item.icon}`;
		},
		listCls() {
			return `${this.loc[this.item.locKey].toLowerCase().replace(/\s/g, '') + '-menu-item'}`
		},
		getIcon() {
			return `${this.item.icon}`;
		},
		itemCls() {
			if (this.item.mode.length === 0) {
				if (this.item.screen === this.currentScreen) {
					return 'current-screen';
				}
			} else if (this.item.mode.includes(this.currentMode) && this.item.screen === this.currentScreen) {
				return 'current-screen';
			}
		},
	},

	watch: {
		currentScreen() {
			this.inGame = extern.inGame;
		}
	}
};
</script><script id="player-name-input" type="text/x-template">
	<input id="player-name" name="name" :value="playerName" v-bind:placeholder="loc.play_enter_name" @change="onNameChange($event)" v-on:keyup="onPlayerNameKeyUp($event)" :class="cls">
</script>

<script>
const PlayerNameInput = {
	template: '#player-name-input',
	props: ['loc', 'playerName', 'pickedGameType', 'currentScreen', 'screens'],
	data: function() {
		return {

		}
	},
	methods: {
		onNameChange (event) {
			console.log('name changed to: ' + event.target.value);
			console.log('play name event handler');
			vueApp.setPlayerName(event.target.value);
			BAWK.play('ui_onchange');
		},
		onPlayerNameKeyUp (event) {
			event.target.value = extern.filterUnicode(event.target.value);
			event.target.value = extern.fixStringWidth(event.target.value);
			event.target.value = event.target.value.substring(0, 128);

			// Send username to server to start the game!
			if (event.code == "Enter" || event.keyCode == 13) {
				if (vueData.playerName.length > 0) {
					if (vueData.playerName.length > 0) {
						vueApp.externPlayObject(vueData.playTypes.joinPublic, this.pickedGameType, this.playerName, -1, '');
					}
				}
			}
		},
	},
	computed: {
		cls() {
			return this.currentScreen === this.screens.profile ? 'font-sigmar text-shadow-black-40 text_white box_relative profile-name' : 'box_absolute ss_field font-nunito ss_name';
		}
	}

};
</script>
<script>
var COMPMAINSIDE = {
	template: '#main-sidebar-template',
	components: {
		// 'house-ad': CompHouseAd,
		'menu-item': MainMenuItem,
		'player-name': PlayerNameInput
	},

	props: ['loc', 'playerName', 'menuItems', 'currentScreen', 'screens', 'mode', 'currentMode', 'inGame', 'isGamePaused', 'pickedGameType'],
	data: function() {
		return {
			itemSelected: 0,
		}
	},		
	methods: {
		onLogoClick(e) {
			if (extern.inGame) {
				e.preventDefault();
				return;
			}
			BAWK.play('ui_click')
		},
		onBackClick() {
			if (this.currentScreen === this.screens.equip) {
				vueApp.onBackClick();
			}
			setTimeout(() => {
				extern.resize();
			}, 1);
			vueApp.switchToGameUi();
			vueApp.showGameMenu();
			vueApp.showRespawnDisplayAd();
		}
	},
};
</script><script id="social-panel-template" type="text/x-template">
	<div ref="socialMediaIcons" class="social_icons roundme_sm f_row justify-content-end gap-sm ss_marginright">
		<!-- <a :href="newsLetterUrl" target="_blank" @click="playSound('newYolker')">
			<div class="icon-wrap bg_blue3 roundme_sm">
				<span class="sr-only">Get the Shell Shocker's Newsletter: The New Yolker</span>
				<i aria-hidden="true" class="text_blue1 fas fa-envelope-open-text"></i>
			</div>
		</a> -->
		<!-- ['name', 'reward', 'url', 'img', 'icon'] -->
		<social-promo ref="socialIcons" v-for="(item, idx) in socialItems" :key="idx" :name="item.name" :reward="item.reward" :url="item.url" :img="item.imgPath" :icon="item.icon" :is-active="item.active" :loc="loc" :is-poki="isPoki" :use-social="socialMedia" :id="item.id"></social-promo>
		<!-- <social-promo ref="socialIcons" :name="showSocialMedia.name" :reward="showSocialMedia.reward" :url="showSocialMedia.url" :img="showSocialMedia.imgPath" :icon="showSocialMedia.icon" :is-active="showSocialMedia.active" :loc="loc" :is-poki="isPoki" :use-social="showSocialMedia.reward" :overlap="adOverlap"></social-promo> -->

	</div>
</script>
<script id="social-promo-template" type="text/x-template">
	<div class="social-media" :class="cls">
		<div v-if="isActive" class="tool-tip" :class="{'active' : isBubbleActive}">
			<a :href="url" target="_blank" :title="urlTitle" class="bg_blue4"  @click="onClickReward()">
				<div class="icon-wrap bg_blue3 roundme_sm text-center">
					<span class="sr-only">Vist Shell Shocker's {{ name }} page</span>
					<i aria-hidden="true" class="text_blue1" :class="useIcon"></i>
				</div>
			</a>
			<div class="tool-tip--bubble" v-show="bubbleHover">
				<div class="tool-tip--group display-grid grid-column-1-2">
					<div class="tool-tip--image box_relative">
						<img v-if="img" class="discord-bubble-img box_absolute" :src="imgSrc" :alt="imgAlt">
					</div>
					<div class="tool-tip--text text-left">
						<section v-html="socialDesc"></section>
					</div>
				</div>
				<!-- .tool-tip--group -->
			</div>
				<!-- .tool-tip--bubble -->
		</div>
		<!-- .tool-tip -->
		<a v-if="!isActive" :href="url" target="_blank" class="bg_blue4" @click="onClickReward()">
			<div class="icon-wrap bg_blue3 roundme_sm text-center">
				<span class="sr-only">Vist Shell Shocker's {{ name }} page</span>
				<i aria-hidden="true" class="text_blue1 fab" :class="useIcon"></i>
			</div>
		</a>
	</div>
</script>


<script>
var COMPSOCIALPROMO = {
	template: '#social-promo-template',
	props: ['name', 'reward', 'url', 'img', 'icon', 'loc', 'isPoki', 'useSocial', 'id'],
	data: function () {
		return {
			isBubbleActive: false,
			bubbleRepeat : '',
			bubbleHover: true,
			isActive: false,
			isItemOwned: false,
			inventoryCheck: 0
		}
	},
	mounted() {
		this.discordBubbleTimer();
	},
	methods: {
		playSound (label) {
			BAWK.play('ui_click');
		},
		discordBubbleTimer() {
			if (this.reward === this.useSocial) {
				if (extern.isGameReady) {
					this.inventoryCheck = 0;
					setTimeout(() => {
						this.isItemOwned = extern.isItemOwned({id: this.id});
						if (!this.isItemOwned) {
							this.isActive = true;
							this.bubbleRepeat = setInterval(() => {
                               this.isBubbleActive = this.isBubbleActive ? false : true;
                       		}, 3000);
						}
					}, 1000);
				} else {
					this.inventoryCheck++;
					if (this.inventoryCheck < 6 && !this.isActive) {
						setTimeout(() => this.discordBubbleTimer(), 2000);
					}
				}
			}
		},

		onClickReward() {
			if (!this.reward) return;
			this.gaSend(this.reward);

			if (!this.isItemOwned && this.reward) {
				extern.socialReward(this.reward);
			}
			this.playSound();
			this.isBubbleActive = false;
			this.bubbleHover = false;

			if (this.bubbleRepeat) {
				clearInterval(this.bubbleRepeat);
			}
		},
		gaSend(label) {
			if (!label) return;
            ga('send', 'event', 'social-buttons', 'click', label);
		}
	},
	computed: {
		itemRedeemed() {
			return localStore.getItem(this.reward + 'Rewarded');
		},
		urlTitle() {
			return `Blue Wizard ${this.name} page`;
		},
		imgAlt() {
			return `Join Blue Wizard's ${this.name} page`;
		},
		imgSrc() {
			return `img/social-media/${this.img}`;
		},
		socialDesc() {
			return this.loc['footer_social_media_' + this.name.toLowerCase()];
		},
		cls() {
			return this.useSocial === this.reward ? `active-social-${this.useSocial.toLowerCase()}` : '';
		},
		useIcon() {
			if (this.name == 'newYolker') {
				return `fas ${this.icon}`;
			} else {
				return `fab ${this.icon}`;
			}
		},
	},
	watch: {
		// useSocial(val) {
		// 	if (!val) {
		// 		return;
		// 	}
		// 	this.isActive = this.reward === val;
		// }
	}
};
</script><script>
var comp_social_panel = {
	template: '#social-panel-template',
	components: {
		'social-promo': COMPSOCIALPROMO
	},
	props: ['loc', 'isPoki', 'socialMedia', 'useSocial'],
	data: function () {
		return {
			// adOverlap: false,
			newsLetterUrl: 'https://bluewizard.com/subscribe-to-the-new-yolker/',
		}
	},
	computed: {
		// showSocialMedia() {
		// 	return this.useSocial[Math.floor(Math.random()*this.useSocial.length)];
		// },
		socialItems() {
			const idx = this.useSocial.findIndex(el => el.reward === this.socialMedia);
			if (idx >= 0) {
				const social = this.useSocial[idx]
				this.useSocial.splice(idx, 1);
				this.useSocial.push(social);
			}
			return this.useSocial;
		}
	}
};
</script><script id="profile-screen-template" type="text/x-template">
	<div id="mainLayout" class="profile-content-wrap">
		<section class="profile-page-content roundme_sm ss_marginright bg_blue6 common-box-shadow">
			<section class="display-grid grid-column-2-eq paddings_xl bg_blue3">
				<header class="f_row align-items-center">
					<section>
						<h1 class="text-shadow-black-40 text_white nospace">{{ playerName }}</h1>
						<!-- <player-name :loc="loc" :player-name="playerName" :picked-game-type="currentGameType" :current-screen="showScreen" :screens="screens"></player-name> -->
						<button v-if="showTwitchEvent" class="ss_button btn-twitch bevel_twitch justify-content-center btn_sm box_relative f_row gap-sm align-items-center" @click="onTwitchDropsClick"><i aria-hidden="true" class="fab fa-twitch"></i> {{ isTwitchLinked }}</button>
					</section>
				</header>
				<aside class="justify-self-end text-right">
					<p class="account-create-date nospace text_white opacity-7" v-html="accountStatus"></p>
					<button id="account-button" @click="onAccountBtnClicked" class="ss_button btn_md font-800" :class="accountBtnCls">{{ accountBtnTxt }}</button>
				</aside>
			</section>
			<section class="profile-stat-wrap center_h paddings_xl box_relative">
				<div v-if="ui.game.stats.loading" class="stats-loading box_absolute">
					<strong><span class="text_blue5 font-size-md text-uppercase">Stats loading... <i class="fas fa-spinner fa-spin"></i></span></strong>
				</div>
				<stats-content :loc="loc" :stats-monthly="statsCurrent" :stats-lifetime="statsLifetime" :kdr="kdr" :kdrLifetime="kdrLifetime" :showLifetime="ui.profile.statTab" :eggs-spent="eggsSpent" :eggs-spent-monthly="eggsSpentMonthly"></stats-content>
			</section>
		</section>
		<small-popup id="loginPopupWarning" ref="loginPopupWarning" @popup-confirm="onQuitAndLoginManage">
			<template slot="header">{{ loc.feedback_account_deletion_title }}</template>
			<template slot="content">
				<div>{{ loginPopupWarningTxt }}</div>
			</template>
			<template slot="cancel">{{ loc.no }}</template>
			<template slot="confirm">{{ loc.yes }}</template>
		</small-popup>
	</div>
	<!-- .main-content -->
</script>

<script id="stats-stats-template" type="text/x-template">
	<div class="stats-box">
		<header class="display-grid stats-grid-other stat-grid-main-header stat-wrapper ss_paddingright_lg ss_paddingleft_xl">
			<div><h3 class="text-shadow-black-40 text_white nospace">Stats</h3></div>
			<div class="text-center"><h3 class="text-shadow-black-40 text_white nospace">{{ loc.stat_lifetime }}</h3></div>
			<div class="text-center"><h3 class="text-shadow-black-40 text_white nospace">{{ loc.stat_monthly }}</h3></div>
		</header>
		<div class="stats-container box_relative center_h ss_margintop_sm">
			<div class="bg_blue2 roundme_lg ss_marginright_sm">
				<div class="stat-wrapper paddings_lg">
					<section v-if="renderReady" class="stat-columns">
						<stat-item :loc="loc" :stat="{'name': 'kills', 'lifetime': statsLifetime.kills.total, 'current': statsMonthly.kills.total }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'deaths', 'lifetime': statsLifetime.deaths.total, 'current': statsMonthly.deaths.total }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'streak', 'lifetime': statsLifetime.streak, 'current': statsMonthly.streak }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'kdr', kdr: true, 'lifetime': [statsLifetime.kills.total, statsLifetime.deaths.total], 'current': [statsMonthly.kills.total, statsMonthly.deaths.total] }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'stat_kotc_wins', 'lifetime': statsLifetime.gameType.kotc.wins, 'current': statsMonthly.gameType.kotc.wins }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'stat_kotc_captured', 'lifetime': statsLifetime.gameType.kotc.captured, 'current': statsMonthly.gameType.kotc.captured }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'stat_eggs_spent', 'lifetime': eggsSpent, 'current': eggsSpentMonthly }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'stat_public_kdr', kdr: true, 'lifetime': [statsLifetime.kills.mode.public, statsLifetime.deaths.mode.public], 'current': [statsMonthly.kills.mode.public, statsMonthly.deaths.mode.public] }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'stat_private_kdr', kdr: true, 'lifetime': [statsLifetime.kills.mode.private, statsLifetime.deaths.mode.private], 'current': [statsMonthly.kills.mode.private, statsMonthly.deaths.mode.private] }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'stat_fa_kdr', kdr: true, 'lifetime': [statsLifetime.kills.gameType.ffa, statsLifetime.deaths.gameType.ffa], 'current': [statsMonthly.kills.gameType.ffa, statsMonthly.deaths.gameType.ffa] }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'stat_teams_kdr', kdr: true, 'lifetime': [statsLifetime.kills.gameType.team, statsLifetime.deaths.gameType.team], 'current': [statsMonthly.kills.gameType.team, statsMonthly.deaths.gameType.team] }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'stat_ctf_kdr', kdr: true, 'lifetime': [statsLifetime.kills.gameType.spatula, statsLifetime.deaths.gameType.spatula], 'current': [statsMonthly.kills.gameType.spatula, statsMonthly.deaths.gameType.spatula] }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'stat_kotc_kdr', kdr: true, 'lifetime': [statsLifetime.kills.gameType.kotc, statsLifetime.deaths.gameType.kotc], 'current': [statsMonthly.kills.gameType.kotc, statsMonthly.deaths.gameType.kotc] }"></stat-item>
					</section>
				</div>
	
				<header class="display-grid stats-grid-other stat-grid-main-header stat-wrapper ss_paddingright_lg ss_paddingleft_xl ss_paddingtop_sm ss_paddingbottom_sm">
					<div><h4 class="nospace">{{ loc.stat_game_mode }}</h4></div>
					<div class="text-center display-grid grid-auto-flow-column" style="margin-left: 0.9em;"><h4 class="nospace">{{ loc.kills }}</h4> <h4 class="nospace">{{ loc.deaths }}</h4></div>
					<div class="text-center display-grid grid-auto-flow-column" style="margin-left: 0.9em;"><h4 class="nospace">{{ loc.kills }}</h4> <h4 class="nospace">{{ loc.deaths }}</h4></div>
				</header>
				<div class="stat-wrapper paddings_lg">
					<section v-if="renderReady" class="stat-columns">
						<stat-item :loc="loc" :stat="{'name': 'stat_public', 'lifetime': [statsLifetime.kills.mode.public, statsLifetime.deaths.mode.public], 'current': [statsMonthly.kills.mode.public, statsMonthly.deaths.mode.public] }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'stat_private', 'lifetime': [statsLifetime.kills.mode.private, statsLifetime.deaths.mode.private], 'current': [statsMonthly.kills.mode.private, statsMonthly.deaths.mode.private] }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'gametype_ffa', 'lifetime': [statsLifetime.kills.gameType.ffa, statsLifetime.deaths.gameType.ffa], 'current': [statsMonthly.kills.gameType.ffa, statsMonthly.deaths.gameType.ffa] }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'gametype_teams', 'lifetime': [statsLifetime.kills.gameType.team, statsLifetime.deaths.gameType.team], 'current': [statsMonthly.kills.gameType.team, statsMonthly.deaths.gameType.team] }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'gametype_ctf', 'lifetime': [statsLifetime.kills.gameType.spatula, statsLifetime.deaths.gameType.spatula], 'current': [statsMonthly.kills.gameType.spatula, statsMonthly.deaths.gameType.spatula] }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'gametype_king', 'lifetime': [statsLifetime.kills.gameType.kotc, statsLifetime.deaths.gameType.kotc], 'current': [statsMonthly.kills.gameType.kotc, statsMonthly.deaths.gameType.kotc] }"></stat-item>
					</section>
				</div>
	
				<header class="display-grid stats-grid-other stat-grid-main-header stat-wrapper ss_paddingright_lg ss_paddingleft_xl ss_paddingtop_sm ss_paddingbottom_sm">
					<div><h4 class="nospace">{{ loc.stat_weapons }}</h4></div>
					<div class="text-center display-grid grid-auto-flow-column" style="margin-left: 0.9em;"><h4 class="nospace">{{ loc.kills }}</h4> <h4 class="nospace">{{ loc.deaths }}</h4></div>
					<div class="text-center display-grid grid-auto-flow-column" style="margin-left: 0.9em;"><h4 class="nospace">{{ loc.kills }}</h4> <h4 class="nospace">{{ loc.deaths }}</h4></div>
				</header>
				<div class="stat-wrapper paddings_lg">
					<section v-if="renderReady" class="stat-columns">
						<stat-item :loc="loc" :stat="{'name': 'item_type_3_0', 'lifetime': [statsLifetime.kills.dmgType.eggk, statsLifetime.deaths.dmgType.eggk], 'current': [statsMonthly.kills.dmgType.eggk, statsMonthly.deaths.dmgType.eggk] }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'item_type_3_1', 'lifetime': [statsLifetime.kills.dmgType.scrambler, statsLifetime.deaths.dmgType.scrambler], 'current': [statsMonthly.kills.dmgType.scrambler, statsMonthly.deaths.dmgType.scrambler] }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'item_type_3_2', 'lifetime': [statsLifetime.kills.dmgType.ranger, statsLifetime.deaths.dmgType.ranger], 'current': [statsMonthly.kills.dmgType.ranger, statsMonthly.deaths.dmgType.ranger] }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'item_type_3_3', 'lifetime': [statsLifetime.kills.dmgType.rpegg, statsLifetime.deaths.dmgType.rpegg], 'current': [statsMonthly.kills.dmgType.rpegg, statsMonthly.deaths.dmgType.rpegg] }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'item_type_3_4', 'lifetime': [statsLifetime.kills.dmgType.whipper, statsLifetime.deaths.dmgType.whipper], 'current': [statsMonthly.kills.dmgType.whipper, statsMonthly.deaths.dmgType.whipper] }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'item_type_3_5', 'lifetime': [statsLifetime.kills.dmgType.crackshot, statsLifetime.deaths.dmgType.crackshot], 'current': [statsMonthly.kills.dmgType.crackshot, statsMonthly.deaths.dmgType.crackshot] }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'item_type_3_6', 'lifetime': [statsLifetime.kills.dmgType.trihard, statsLifetime.deaths.dmgType.trihard], 'current': [statsMonthly.kills.dmgType.trihard, statsMonthly.deaths.dmgType.trihard] }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'weapon_cluck_9mm', 'lifetime': [statsLifetime.kills.dmgType.pistol, statsLifetime.deaths.dmgType.pistol], 'current': [statsMonthly.kills.dmgType.pistol, statsMonthly.deaths.dmgType.pistol] }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'item_type_6', 'lifetime': [statsLifetime.kills.dmgType.grenade, statsLifetime.deaths.dmgType.grenade], 'current': [statsMonthly.kills.dmgType.grenade, statsMonthly.deaths.dmgType.grenade] }"></stat-item>
						<stat-item :loc="loc" :stat="{'name': 'item_type_7', 'lifetime': [statsLifetime.kills.dmgType.melee, statsLifetime.deaths.dmgType.melee], 'current': [statsMonthly.kills.dmgType.melee, statsMonthly.deaths.dmgType.melee] }"></stat-item>
					</section>
				</div>
			</div>
		</div>
	</div>
</script>

<script id="the-stat-template" type="text/x-template">
	<section class="stat display-grid font-600" :class="statWrapper">
		<div class="text_white roundme_sm_top-bottom_right ss_paddingleft ss_paddingtop_micro ss_marginbottom_sm"><p>{{ statName }}</p></div>
		<div class="text-center text_white roundme_sm_top-bottom_left ss_paddingleft ss_paddingtop_micro ss_marginbottom_sm ss_marginright_sm" :class="lifetimeCls" v-html="statLifetime"></div>
		<div class="text-center text_white roundme_sm ss_paddingleft ss_paddingtop_micro ss_marginbottom_sm" :class="currentCls" v-html="statMonthly"></div>
	</section>
</script>

<script>
var StatTemplate = {
    template: '#the-stat-template',
    props: ['loc', 'stat'],
    data: function () {
        return {
			lifetimeCls: '',
			currentCls: '',
		}
    },
    methods: {
		statName(key) {
			if (key=== 'eggk') {
				key= 'eggK-47';
			}
			return key.toUpperCase();
		},
		kdr(kills, deaths) {
			return Math.floor((kills / Math.max(deaths, 1)) * 100) / 100;
		},
		setupStat(stat) {
			if (this.stat.kdr !== undefined && stat.length !== undefined) {
				return this.kdr(stat[0], stat[1]);
			} else if (stat && stat.length !== undefined) {
				return `<div>${stat[0]}</div> <div>${stat[1]}</div>`;
			} else {
				return stat;
			}
		}
    },
	computed: {
		statName() {
			// if (this.stat.name === 'eggk') {
			// 	return 'eggK-47';
			// }
			return this.loc[this.stat.name];
			// return this.stat.name;
		},
		statMonthly() {
			if (this.stat.current && this.stat.current.length !== undefined && this.stat.kdr === undefined) {
				this.currentCls = 'display-grid grid-column-2-eq';
			}
			return this.setupStat(this.stat.current);
		},
		statLifetime() {
			if (hasValue(this.stat.lifetime) && this.stat.lifetime.length !== undefined && this.stat.kdr === undefined) {
				this.lifetimeCls = 'display-grid grid-column-2-eq';
			}
			return this.setupStat(this.stat.lifetime);
		},
		statWrapper() {
			// return this.stat.lifetime.length !== undefined && this.stat.kdr === undefined ? 'stats-grid-other' : 'stats-grid';
			return 'stats-grid-other';
		},
	},
};
</script>
<script>
var STATSPOPUP = {
    template: '#stats-stats-template',
    props: ['loc', 'statsMonthly', 'statsLifetime', 'kdr', 'kdrLifetime', 'showLifetime', 'eggsSpent', 'eggsSpentMonthly'],
	components: {
		'stat-item': StatTemplate
	},
    methods: {
		statName(key) {
			if (key === 'eggk') {
				key = 'eggK-47';
			}
			return key.toUpperCase();
		},
		kdr(kills, deaths) {
			return Math.floor((kills / Math.max(deaths, 1)) * 100) / 100;
		}
    },
	computed: {
		// VUE is taking it's time passing these props, so we need to wait for them to be ready
		renderReady() {
			return Object.keys(this.statsLifetime).length > 0 && Object.keys(this.statsMonthly).length > 0;
		}
	}
};
</script>
<script>
const ProfileScreen = {
	template: '#profile-screen-template',
	components: {
		'stats-content': STATSPOPUP,
		// 'player-name': PlayerNameInput
	},

	props: ['loc'],

	data: function () {
		return vueData;
	},

	methods: {
		showSignIn: function () {
			extern.showSignInDialog();
			vueApp.$refs.firebaseSignInPopup.show();
		},
		onAccountBtnClicked: function () {
			if (this.showAdBlockerVideoAd) {
				return;
			}

			if (extern.inGame) {
				this.$refs.loginPopupWarning.show();
			} else {
				if (this.isAnonymous && this.showSignIn) {
				vueApp.onSignInClicked();
				} else {
					vueApp.onSignOutClicked();
				}
			}
		},

		onQuitAndLoginManage() {
			vueApp.onLeaveGameConfirm();
			setTimeout(() => {
				this.$emit('leave-game-confimed');
			}, 1000);
		},

		onTwitchDropsClick() {
			if (this.showAdBlockerVideoAd) {
				return;
			}

			ga('send', 'event', 'home_event_btn', 'click', 'twitch_drops');
			window.open(dynamicContentPrefix + 'twitch');
		},
		selectTab(val) {
			if (this.ui.profile.statTab === val) {
				return;
			}
			this.ui.profile.statTabClicked = true;
			setTimeout(() => this.ui.profile.statTabClicked = false, 500);
			BAWK.play('ui_toggletab');
			this.ui.profile.statTab = val;
		}
	},
	computed: {
		isEggStoreSaleItem() {
            return this.eggStoreItems.some( item => item['salePrice'] !== '');
        },
		showTwitchEvent() {
			return this.ui.eventData.current && this.ui.eventData.current === 'twitch-drops';
		},
		isTwitchLinked() {
			return this.twitchLinked ? this.loc.account_linked_to_twitch : this.loc.account_link_to_twitch;
		},
		accountStatus() {
			if (this.accountCreated) {
				return `${this.loc.account_created}: <strong>${this.accountCreated}</strong>`;	
			}
			return this.loc.account_profile_login_desc;
		},
		accountBtnCls() {
			if (this.isAnonymous && this.showSignIn) {
				return 'btn_green bevel_green';
			}
			return 'btn_yolk bevel_yolk';
			
		},
		accountBtnTxt() {
			if (this.isAnonymous && this.showSignIn) {
				return this.loc.sign_in;
			}
			return this.loc.sign_out;
		},
		loginPopupWarningTxt(){
			return this.isAnonymous && this.showSignIn ? this.loc.sign_in_and_leave_game : this.loc.sign_out_and_leave_game;
		}
	}
};
</script>
<script>
var comp_home_screen = {
	template: '#home-screen-template',
	components: {
		'play-panel': comp_play_panel,
		'house-ad-big': comp_house_ad_big,
		'house-ad-small': comp_house_ad_small,
		'account-panel': comp_account_panel,
		'chicken-panel': comp_chicken_panel,
		'footer-links-panel': comp_footer_links_panel,
		'event-panel': comp_events,
		'media-tabs': MEDIATABS,
		'main-sidebar': COMPMAINSIDE,
		'profile-screen': ProfileScreen,
		'social-panel': comp_social_panel
		// 'pwa-button': comp_pwa_btn,
	},

	data: function () {
		return vueData;
	},

	methods: {
		playSound (sound) {
			BAWK.play(sound);
		},
		onEquipClicked: function () {
			if (this.showAdBlockerVideoAd) {
				return;
			}
			vueApp.switchToEquipUi();
			BAWK.play('ui_equip');
		},
		showSignIn: function () {
			extern.showSignInDialog();
			vueApp.$refs.firebaseSignInPopup.show();
		},
		onSignInClicked: function () {
			if (this.showAdBlockerVideoAd) {
				return;
			}
			this.showSignIn();
		},
		onSignOutClicked: function () {
			if (this.showAdBlockerVideoAd) {
				return;
			}
			extern.signOut();
		},
		leaveGameConfirmed() {
			if (this.showAdBlockerVideoAd) {
				return;
			}

			if (this.isAnonymous && this.showSignIn) {
				this.onSignInClicked();
			} else {
				extern.signOut();
			}
		},
		onResendEmailClicked: function () {
			extern.sendFirebaseVerificationEmail();

			vueApp.showGenericPopup('verify_email_sent', 'verify_email_instr', 'ok');
		},
		onBigHouseAdClosed: function () {
			console.log('big house ad closed event received');
			this.ui.houseAds.big = null;
			this.urlParamSet = this.urlParams ? true : null;
			vueApp.showTitleScreenAd();
            vueApp.shellShockUrlParamaterEvents();
		},
		onPlayerNameChanged: function (newName) {
			console.log('play name event handler');
			vueApp.setPlayerName(newName);
			BAWK.play('ui_onchange');
		},
		onEggPlayClick() {
            play: 'play game',
			ga('send', 'event', vueData.googleAnalytics.cat.play, vueData.googleAnalytics.action.eggDollClick);
			vueApp.$refs.homeScreen.$refs.playPanel.onPlayButtonClick();
		},
		api_incentivizedVideoRewardRequest() {
			extern.api_incentivizedVideoRewardRequested();
		},
		onTwitchDropsClick() {
			if (this.showAdBlockerVideoAd) {
				return;
			}
			window.open(dynamicContentPrefix + 'twitch');
		},
		chwStopCycle() {
			if (this.chwHomeTimer) {
				clearInterval(this.chwHomeTimer);
				this.chwHomeTimer = '';
				this.chwHomeEl.classList.remove('.active');
			}
		},
		onGameTypeChanged(val) {
			vueApp.onGameTypeChanged(val);
		}
	},
	computed: {
		isEggStoreSaleItem() {
            return this.eggStoreItems.some( item => item['salePrice'] !== '');
        },
		twitchDropsBtnImgSrs() {
			if (this.twitchLinked) {
				return '../img/events/twitch-drops-btn-linked.png';
			}
			return '../img/events/twitch-drops-btn-link-now.png';
		},
		isTwitchLinked() {
			if (this.twitchLinked) return '<i class="fas fa-check-circle text_twitch_yellow"></i>';
			return '<i class="fas fa-times-circle text_grey"></i>';
		}
	}
};
</script><script id="equip-screen-template" type="text/x-template">
	<div id="#equip_wrapper" :class="screenCls">
		<div class="home-main-wrapper display-grid box_absolute height-100vh">
			<section id="equip_panel_middle" class="equip_panel middle_panel box_relative align-items-center">
				<div v-if="showShopUi" class="panel_tabs display-grid grid-column-3-eq gap-sm">
					<button class="ss_bigtab bevel_blue roundme_md font-sigmar" :class="getButtonToggleClass(equipMode.shop)" @click="switchTo(equipMode.shop)">{{ loc.eq_shop }}</button>
					<button class="ss_bigtab bevel_blue roundme_md font-sigmar" :disabled="!accountSettled" :class="getButtonToggleClass(equipMode.featured)" @click="switchTo(equipMode.featured)">{{ loc.eq_featured }}</i></button>
					<button class="ss_bigtab bevel_blue roundme_md font-sigmar" :disabled="!accountSettled" :class="getButtonToggleClass(equipMode.skins)" @click="switchTo(equipMode.skins)">{{ loc.eq_skins }}</i></button>
				</div>
				<div v-show="isOnShopInventoryLimited" id="equip_purchase_top" class="equip_purchase_top">
					<physical-tag id="physical-tag" class="ss_marginright" v-if="equip.physicalUnlockPopup.item" ref="physical-tag" :loc="loc" :item="equip.physicalUnlockPopup.item" @buy-item-clicked="onBuyItemClicked"></physical-tag>
				</div>

				<div v-show="isEquipModeInventory || isOnEquipModeSkins" id="equip_weapon_panel">
					<weapon-select-panel id="weapon_select" ref="weapon_select" :loc="loc" :account-settled="accountSettled" :play-clicked="playClicked" :current-class="classIdx" :current-screen="showScreen" :screens="screens" @changed-class="onChangedClass"></weapon-select-panel>
				</div>
				<color-select v-if="isEquipModeInventory || isOnEquipModeSkins" id="equip.equipped_slots" ref="colorSelect" :loc="loc" :is-upgrade="isUpgraded" :color-idx="equip.colorIdx" :extra-colors-locked="equip.extraColorsLocked" @color-changed="onColorChanged" :current-screen="showScreen" :screens="screens"></color-select>
				<div id="limited-un-vaulted" v-show="isOnEquipModeFeatured || isEquipModeShop" class="limited-un-vaulted centered_x bottom-1 bg_blue2 roundme_lg paddings_lg">
					<item-grid id="item_grid" ref="item_grid" :loc="loc" :items="equip.showUnVaultedItems" :has-buy-btn="false" :selectedItem="equip.selectedItem" @item-selected="onItemSelected" :in-shop="isInShop" :in-inventory="isEquipModeInventory"></item-grid>
					<h4 class="text-center text-uppercase nospace text_blue5">{{ loc.eq_unvaulted_limited_msg }}</h4>
					<div class="price-tag-wrap">
						<price-tag id="price_tag" v-if="equip.selectedItem && isSelectedInUnVaulted" ref="price_tag" :loc="loc" :item="equip.selectedItem" :hide-get-more-eggs="true" :egg-total="eggs" @buy-item-clicked="onBuyItemClicked" :chw-ready="chicknWinnerReady"></price-tag>
					</div>

				</div>
			</section>
			<!-- end .middle_panel -->
			<section id="equip_panel_right" class="equip_panel right_panel">
				<h3 v-if="!showPurchasesUi" class="equip-title text-center margins_sm box_relative text_white text-shadow-black-40 nospace">{{ loc[equip.categoryLocKey] }}</h3>
				<div v-if="isOnEquipModeFeatured" class="limited-msg-wrapper box_relative display-grid align-items-center text-center text_vip_yellow bg-limited roundme_md">
					<p class="nospace">{{ loc.eq_limited_msg }}</p>
				</div>
				<item-type-selector v-if="isOnShopInventory" class="box_relative" id="item_type_selector" ref="item_type_selector" :items="ui.typeSelectors" :in-limited="isOnEquipModeFeatured" :selected-item-type="equip.selectedItemType" :show-special-items="equip.showSpecialItems" :in-shop="isInShop" @item-type-changed="onItemTypeChanged" @reset-filter="onTaggedResetItems"></item-type-selector>
				<egg-store v-if="isEquipModeShop" :loc="loc" :products="eggStoreItems" :sale-event="isSale"></egg-store>
				<div v-if="currentEquipMode !== equipMode.shop" id="equip_sidebox" class="roundme_md box_relative ss_marginbottom_lg">
					<div id="item_mask"></div>
					<item-grid v-if="isOnShopInventoryLimited" id="item_grid" ref="item_grid" :loc="loc" :class="gridCls" :items="equip.showingItems" :selectedItem="equip.selectedItem" :category-loc-key="equip.categoryLocKey" :in-shop="isInShop" :in-inventory="isEquipModeInventory" @item-selected="onItemSelected" @switch-to-skins="onSwitchToSkinsClicked"></item-grid>
					<!--<house-ad-small id="banner-ad" v-show="!isInShop"></house-ad-small>-->
				</div>
				<div v-show="isEquipModeInventory" class="ss_paddingright">
					<button class="ss_button btn_blue bevel_blue box_relative fullwidth text-uppercase" @click="onRedeemClick">Redeem Code</button>
				</div>
				<price-tag id="price_tag" v-if="showPriceTag" ref="price_tag" :loc="loc" :item="equip.buyingItem" :egg-total="eggs" @buy-item-clicked="onBuyItemClicked" :chw-ready="chicknWinnerReady"></price-tag>
			</section>
			<!-- .right_panel-->
		</div>

		<!-- Popup: Buy Item -->
		<small-popup id="buyItemPopup" ref="buyItemPopup" @popup-confirm="onBuyItemConfirm">
			<template slot="header">{{ loc.p_buy_item_title }}</template>
			<template slot="content">
				<div>
					<canvas id="buyItemCanvas" ref="buyItemCanvas" width="250" height="250"></canvas>
				</div>
				<div class="f_row f_center">
					<img v-if="!isBuyingItemPrem" src="img/ico_goldenEgg.png" class="egg_icon"/>
					<i v-else class="fas fa-dollar-sign"></i>
					<h1>{{ (equip.buyingItem) ? equip.buyingItem.price : '' }}</h1>
				</div>
			</template>
			<template slot="cancel">{{ loc.p_buy_item_cancel }}</template>
			<template slot="confirm">{{ loc.p_buy_item_confirm }}</template>
		</small-popup>

		<!-- Popup: Redeem Code -->
		<small-popup id="redeemCodePopup" ref="redeemCodePopup" :popup-model="equip.redeemCodePopup" @popup-confirm="onRedeemCodeConfirm">
			<template slot="header">{{ loc.p_redeem_code_title }}</template>
			<template slot="content">
				<div class="error_text shadow_red" v-show="equip.redeemCodePopup.showInvalidCodeMsg">{{ loc.p_redeem_code_no_code }}</div>
				<p><input type="text" class="ss_field ss_margintop ss_marginbottom text-center width_lg" v-model="equip.redeemCodePopup.code" v-bind:placeholder="loc.p_redeem_code_enter"></p>
			</template>
			<template slot="cancel">{{ loc.cancel }}</template>
			<template slot="confirm">{{ loc.confirm }}</template>
		</small-popup>

		<!-- Popup: Physical Unlock -->
		<small-popup id="physicalUnlockPopup" ref="physicalUnlockPopup" :popup-model="equip.physicalUnlockPopup" @popup-confirm="onPhysicalUnlockConfirm">
			<template slot="header">{{ loc.p_physical_unlock_title }}</template>
			<template slot="content">
				<div v-if="(equip.physicalUnlockPopup.item !== null)">
					<div>
						<item :loc="loc" :item="equip.physicalUnlockPopup.item" :isSelected="false" :show-item-only="true"></item>
						<div class="f_row f_center">
							<img src="img/ico_goldenEgg.png" class="egg_icon"/>
							<h1>{{ loc.p_buy_special_price }}</h1>
						</div>
					</div>
					<div class="popup_sm__item_desc">
						{{ loc[equip.physicalUnlockPopup.item.item_data.physicalUnlockLocKey] }}
					</div>
				</div>
			</template>
			<template slot="cancel">{{ loc.cancel }}</template>
			<template slot="confirm">{{ loc.confirm }}</template>
		</small-popup>
	</div>
</script>

<script id="equipped-slots-template" type="text/x-template">
	<div>
		<h3 class="margins_sm">{{ loc.eq_equipped }}</h3>
		<div id="equip_equippedslots">
			<div class="equip_item roundme_lg clickme f_row f_center" @click="onClick(itemType.Primary)">
				<item :loc="loc" id="primary_item" ref="primary_item" v-if="primaryItem" :item="primaryItem" class="equip_icon" :equippedSlot="true"></item>
				<div v-if="!primaryItem" class="equip_icon equip_icon_hat equip_icon_no_item"><img src="img/inventory-icons/ico_weaponPrimary.svg" alt="Primary equip slot"></div>
			</div>

			<div class="equip_item roundme_lg clickme f_row f_center" @click="onClick(itemType.Secondary)">
				<item :loc="loc" id="secondary_item" ref="secondary_item" v-if="secondaryItem" :item="secondaryItem" class="equip_icon" :equippedSlot="true"></item>
				<div v-if="!secondaryItem" class="equip_icon equip_icon_hat equip_icon_no_item"><img src="img/inventory-icons/ico_weaponSecondary.svg" alt="Secondary equip slot"></div>
			</div>

			<div class="equip_item roundme_lg clickme f_row f_center" @click="onClick(itemType.Grenade)">
				<item :loc="loc" id="grenade_item" ref="grenade_item" v-if="grenadeItem" :item="grenadeItem" class="equip_icon" :equippedSlot="true"></item>
				<div v-if="!grenadeItem" class="equip_icon equip_icon_hat equip_icon_no_item"><img src="img/inventory-icons/ico_grenade.svg" alt="Grenade equip slot"></div>

			</div>
			
			<div class="equip_item roundme_lg clickme f_row f_center" @click="onClick(itemType.Hat)">
				<item :loc="loc" id="hat_item" ref="hat_item" v-if="hatItem" :item="hatItem" :equippedSlot="true"></item>
				<div v-if="!hatItem" class="equip_icon equip_icon_hat equip_icon_no_item"><img src="img/inventory-icons/ico_hat.svg" alt="Hat item slot"></div>
			</div>
			
			<div class="equip_item roundme_lg clickme f_row f_center" @click="onClick(itemType.Stamp)">
				<item :loc="loc" id="stamp_item" ref="stamp_item" v-if="stampItem" :item="stampItem" :equippedSlot="true"></item>
				<div v-if="!stampItem" class="equip_icon equip_icon_stamp equip_icon_no_item"><img src="img/inventory-icons/ico_stamp.svg" alt="Stamp item slot"></div>
			</div>
		</div>
	</div>
</script>

<script>
var comp_equipped_slots = {
	template: '#equipped-slots-template',
	components: { 'item': comp_item },
	props: ['loc', 'primaryItem', 'secondaryItem', 'hatItem', 'stampItem', 'grenadeItem'],

	data: function () {
		return {
			itemType: ItemType
		}
	},

	methods: {
		onClick: function (itemType) {
			this.$emit('equipped-type-selected', itemType);
		}
	},

	computed: {
		emptyHatClass: function () {
			return (this.hatItem === null) ? 'equip_icon_hat' : '';
		},

		emptyStampClass: function () {
			return (this.stampItem === null) ? 'equip_icon_stamp' : '';
		}
	}
};
</script><script id="color-select-template" type="text/x-template">
	<div class="text-center egg-color-select roundme_sm common-box-shadow">
		<!-- <h3 class="margins_sm">{{ loc.eq_color }}</h3> -->
		<div id="equip_free_colors" class="display-grid text-center grid-auto-flow-column align-items-center ">
			<!-- <svg v-for="(c, index) in freeColors" class="eggIcon equip_color" :style="{ color: c }" :class="isSelectedClass(index)" @click="onClick(index)"><use xlink:href="#icon-egg"></use></svg> -->
			<span v-for="(c, index) in freeColors" class="box_relative roundme_sm egg-color-icon " @click="onClick(index)">
				<img v-if="index === colorIdx" class="centered_x color-select-arrow" src="img/svg/ico-arrow-colorPicker.svg"/>
				<i  class="fas fa-egg" :style="{ color: c }" :class="isSelectedClass(index)"></i>
			</span>
		<!-- </div>
		<div id="equip_paid_colors" class="display-grid text-center grid-auto-flow-column align-items-center "> -->

			<!-- <svg v-for="(c, index) in paidColors" class="eggIcon equip_color" :style="{ color: c }" :class="isSelectedClass(index + freeColors.length)" @click="onClick(index + freeColors.length)"><use :xlink:href="getExtraColorEggIcon"></use></svg> -->
			<span v-for="(c, index) in paidColors" class="box_relative roundme_sm egg-color-icon" @click="onClick(index + freeColors.length)">
				<img v-if="colorIdx === (index + freeColors.length)" class="centered_x color-select-arrow" src="img/svg/ico-arrow-colorPicker.svg"/>
				<span class="fa-stack roundme_sm" :class="isSelectedClass(index + freeColors.length)">
					<i class="fas fa-egg fa-stack-1x" :style="{ color: c }"></i>
					<i v-if="!isUpgrade" class="fas fa-lock fa-stack-1x text_white"></i>
				</span>
			</span>
		</div>
	</div>
</script>

<script>
var comp_color_select = {
	template: '#color-select-template',
	props: ['loc', 'colorIdx', 'extraColorsLocked', 'isUpgrade'],
	
	data: function () {
		return {
			freeColors: freeColors,
			paidColors: paidColors
		}
	},

	methods: {
		isSelectedClass: function (idx) {
			return (idx === this.colorIdx) ? 'selected' : ''
		},

		onClick: function (idx) {
			if (idx >= freeColors.length && this.extraColorsLocked === true) {
				vueApp.showSubStorePopup();
				// BAWK.play('ui_chicken');
				return;
			}

			this.$emit('color-changed', idx);
		},
	},

	computed: {
		getExtraColorEggIcon() {
			return (this.extraColorsLocked === true && !this.isUpgrade) ? '#icon-egg-locked' : '#icon-egg';
		},
	}
};
</script><script id="item-timer-template" type="text/x-template">
	<div>
		<div id="equip_timerem" class="box_blue3 roundme_sm shadow_blue4">
			<i class="fas fa-hourglass-start"></i> 9{{ loc.eq_day }}<span class="blink">:</span>12{{ loc.eq_hour }}
			<br>{{ loc.eq_remaining }}
		</div>
	</div>
</script>

<script>
var comp_item_timer = {
	template: '#item-timer-template',
	props: ['loc']
};
</script><script id="price-tag-template" type="text/x-template">
	<div id="equip_purchase_items" v-if="isNoPrice" class="equip_purchase_items box_relative">

		<button v-if="!playerNeedsMoreEggs && !isItemOwned" class="ss_button btn_green bevel_green btn_md btn_buy_item display-grid fullwidth font-nunito f_row align-items-center justify-content-center" @click="onBuyClick">
			{{priceTagText}}
			<img v-if="!isPremium" src="img/ico_goldenEgg.png" class="ss_marginright_sm ss_marginleft">
			<i v-else class="fas fa-dollar-sign ss_marginleft"></i>
			{{ item.price }}
		</button>

		<button v-show="playerNeedsMoreEggs && !isItemOwned" class="ss_button btn_yolk bevel_yolk text-shadow-black-40 fullwidth text-center" @click="onWatchAdsClick">{{ watchAdsTxt }}!</button>
		<button v-show="!hideGetMoreEggs && !isItemOwned" class="ss_button btn_blue bevel_blue text-shadow-black-40 fullwidth text-center" @click="onGetMoreEggsClick">{{ loc.account_title_eggshop }}</button>
	</div>
</script>

<script>
var comp_price_tag = {
	template: '#price-tag-template',
	props: ['loc', 'item', 'eggTotal', 'chwReady', 'hideGetMoreEggs'],
	data() {
		return {
		};
	},

	methods: {
		onBuyClick: function () {
			if (this.isPremium) {
				return vueApp.showPopupEggStoreSingle(this.item.sku);
			}
			this.$emit('buy-item-clicked', this.item);
		},
		onWatchAdsClick() {
			vueApp.showNuggyPopup();
		},
		onGetMoreEggsClick() {
			vueApp.switchToEquipUi(vueApp.equipMode.shop)
		}
	},
	computed: {
		isPremium() {
			return this.item.unlock === 'premium';
		},
		isNoPrice() {
			return this.item.price < 1000000000;
		},
		playerNeedsMoreEggs() {
			if (this.isPremium) {
				return false;
			} else {
				return this.item.price > this.eggTotal;
			}
		},
		priceTagText() {
			if (!this.playerNeedsMoreEggs) {
				return this.loc.eq_buy;
			}
			return;
		},
		priceTagTextCls() {
			if (!this.playerNeedsMoreEggs) {
				return 'text_blue5';
			} else {
				return 'text_red';
			}
		},
		watchAdsTxt() {
			if (this.chwReady) {
				return this.loc.chw_btn_watch_ad;
			} else {
				return 'Wait for more eggs';
			}
		},
		isItemOwned() {
			return extern.isItemOwned(this.item)
		}
	}
};
</script><script id="physical-tag-template" type="text/x-template">
	<div id="equip_get_physical_item" class="equip_purchase_items">
		<div id="equip_pricetag" class="equip_pricetag shadow_blue2">
			<img src="img/pricetag_left.png">
			<div class="equip_pricetag__tag equip_pricetag__is_special_tag">
				<img src="img/ico_goldenEgg.png" class="ss_marginright">{{ loc.p_buy_special_price }}
			</div>
			<img src="img/pricetag_right.png">
		</div>
		<button class="ss_button btn_yolk bevel_yolk is_special_get_btn" @click="onBuyClick">{{ loc.p_chicken_goldbutton }}</button>
	</div>
</script>

<script>
var comp_physical_tag = {
	template: '#physical-tag-template',
	props: ['loc', 'item'],

	methods: {
		onBuyClick: function () {
			this.$emit('buy-item-clicked', this.item);
		}
	}
};
</script><script id="item-type-selector-template" type="text/x-template">
	<div class="ss_marginbottom_sm">
		<div id="equip_itemtype" class="equip_panelhead display-grid grid-auto-flow-column align-items-center f_space_between">
			<div v-for="item in items" class="ico_itemtype clickme roundme_sm f_row align-items-center" :class="{'selected' : item.type === selected || item.type === selectedItemType}" @click="onItemTypeClick(item.type)">
				<svg>
					<use :xlink:href="item.img"></use>
				</svg>
			</div>
		</div>
	</div>
</script>

<script>
var comp_item_type_selector = {
	template: '#item-type-selector-template',
	props: ['items', 'showSpecialItems', 'selectedItemType', 'inShop', 'inLimited'],

	data: function () {
		return {
			showingTagged: false,
			filter: false,
			selected: null
		}
	},

	methods: {
		onItemTypeClick: function (itemType) {

			this.selected = itemType;

			this.$emit('item-type-changed', itemType);
		},
		// resetFilter() {
		// 	if (this.filter ) {
		// 		this.filter = false;
		// 		this.selected = null;
		// 		this.$emit('reset-filter');
		// 	}
		// }
	},
	computed: {
		filterTxt() {
			return this.filter ? 'RESET': '';
		}
	},
	watch: {
		selectedItemType() {
			this.selected = null;
			this.filter = false;
		}
	}
};
</script><script id="item-grid-template" type="text/x-template">
	<div>
		<div v-if="accountSettled" id="equip_grid" :class="gridCls" class="display-grid">
			<item v-for="i in itemsSorted" :loc="loc" :item="i" :key="i.id" :isSelected="isSelected(i)" :has-buy-btn="hasBuyBtn" @item-selected="onItemSelected" :is-shop="inShop"></item>
			<div v-show="inInventory" class="grid-item roundme_sm clickme morestuff box_relative common-box-shadow btn_green bevel_green f_row align-items-center justify-content-center" @click="onSwitchToSkinsClick">
				<icon class="fill-white shadow-filter" name="ico-nav-shop"></icon>
			</div>
			<div v-show="inShop && !hiddenPremItemCheck" class="grid-item roundme_lg box_absolute centered soldout text-center">
				<div class="soldout_head shadow_bluebig5" v-html="loc.eq_sold_out_head"></div>
				<div class="soldout_text shadow_bluebig5" v-html="loc.eq_sold_out_text"></div>
			</div>
		</div>
		<div id="items-account-not-loaded" v-show="!accountSettled" class="text-center">
			<h3>{{loc.signin_auth_title}}</h3>
			<p>{{loc.signin_auth_msg}}</p>
		</div>
	</div>
</script>

<script>
var comp_item_grid = {
	template: '#item-grid-template',
	components: { 'item': comp_item },
	props: ['loc', 'items', 'selectedItem', 'gridClass', 'categoryLocKey', 'inShop', 'hasBuyBtn', 'inInventory'],
	data() {
		return vueData;
	},

	methods: {
		onItemSelected: function (selectedItem) {
			this.$emit('item-selected', selectedItem);
			BAWK.play('ui_click');
		},

		isSelected: function (item) {
			if (!hasValue(this.selectedItem)) {
				return false;
			}

			return (this.selectedItem.id === item.id);
		},

		onSwitchToSkinsClick: function () {
			this.$emit('switch-to-skins');
			BAWK.play('ui_playconfirm');
		},

		isPremItemInStore() {
			let hasItem = false;
			for (let i = 0; i < this.items.length; i++) {
				const item = this.items[i];
					const isItem = vueApp.premiumShopItems.find(i => i.isActive && i.itemId.id === item.id);
					if (hasValue(isItem)) {
						hasItem = true;
						break;
					}
			}
			return hasItem;
		}
	},

	computed: {
		categoryName: function () {
			if (!hasValue(this.selectedItem)) {
				return null;
			}

			return this.loc['item_type_' + this.selectedItem.item_type_id];
		},

		hiddenPremItemCheck() {
			if (this.items.some(i => i.unlock === 'purchase') || this.items.length !== 0) return true;
			if (this.items.some(i => i.unlock === 'premium')) return this.isPremItemInStore();
			return false;
		},

		gridCls() {
			if (this.hasBuyBtn) {
				return 'grid-auto-flow-column gap-1';
			} else {
				return 'grid-column-3-eq align-content-start align-content-start gap-sm';
			}
		},
		itemsSorted() {
			return this.items.sort((b, a) => {
				// if (a.id === this.selectedItem.id && b.id !== this.selectedItem.id && !this.inShop) return 1;
				// if (a.id !== this.selectedItem.id && b.id === this.selectedItem.id && !this.inShop) return -1;
				if (a.unlock === 'premium' && b.unlock !== 'premium') return 1;
				if (a.unlock !== 'premium' && b.unlock === 'premium') return -1;
				if (a.unlock === 'vip' && b.unlock !== 'vip') return 1;
				if (a.unlock !== 'vip' && b.unlock === 'vip') return -1;
				if (a.unlock === 'physical' && b.unlock !== 'physical') return 1;
				if (a.unlock !== 'physical' && b.unlock === 'physical') return -1;
				if (a.unlock === 'manual' && b.unlock !== 'manual') return 1;
				if (a.unlock !== 'manual' && b.unlock === 'manual') return -1;
				if (a.unlock === 'default' && b.unlock !== 'default') return 1;
				if (a.unlock !== 'default' && b.unlock === 'default') return -1;
				if (a.unlock === 'purchase' && b.unlock !== 'purchase') return 1;
				if (a.unlock !== 'purchase' && b.unlock === 'purchase') return -1;
				return 0;
			});
		}
	}
};
</script><script id="egg-store-template" type="text/x-template">
	<div class="f_col">
		<egg-store-item v-for="item in products" :key="item.sku" :item="item" :loc="loc" inStore="true" :account-set="accountSettled" :isUpgraded="isUpgraded" :isSaleEvent="saleEvent"></egg-store-item>
	</div>
</script>

<template id="comp-store-item">
    <div v-if="showItem" class="single-egg-store-item box_relative align-items-center roundme_md common-box-shadow" :class="[itemCls, {purchased: purchased}]">
		<header v-if="this.item.type === 'item'" class="grid-span-2-start-1">
			<h6 class="nospace text-left f_row align-items-center f_space_between">
				{{ loc[title] }}
				<icon class="egg-store-item-type" :name="getItemInfo"></icon>
			</h6>
		</header>
		<div class="display-grid grid-column-1-2 align-items-center">
			<div>
				<img :src="img" class="eggshop_image roundme_md display-block center_h">
				<div v-if="this.item.type === 'item'" class="text-center">
					<div class="eggshop_pricebox text_brown" :class="{ slashed: item.salePrice }">
						${{ price }} <span>USD</span>
					</div>
				</div>
			</div>
			<div>
				<header v-if="this.item.type !== 'item'">
					<h6 class="eggshop_bigtitle nospace text_blue5">{{ loc[title] }}</h6>
				</header>
				<div class="display-grid">
					<p v-if="showSubtitle" class="eggshop_subtitle" :class="subTitleCls">{{ loc[description] }}</p>
				</div>
				<div :class="{'display-grid grid-column-2-eq align-items-center': this.item.type !== 'item'}">
					<div v-if="this.item.type !== 'item'" class="eggshop_pricebox">
						<p v-if="item.salePrice" class="eggshop_pricebox nospace slashed"><span class="eggshop-dollar-sign">$</span>{{ item.salePrice }} <span class="eggshop-currency-type">USD</span></p>
						<p class="eggshop_pricebox nospace text_blue5"><span class="eggshop-dollar-sign">$</span>{{ item.price }}<span class="eggshop-currency-type"> USD</span></p>
					</div>
					<button class="btn_store ss_button btn_green bevel_green btn_sm" @click="onItemClicked()">{{ buyBtnText }}</button>
				</div>
				<!-- <div v-if="item.type === 'item' && !hasPurchased && inStore"> -->
					<button v-if="item.type === 'item' && !hasPurchased && inStore" class="ss_button btn_yolk bevel_yolk btn_sm center_h vip-get-btn f_row align-items-center" @click="onVipClick"><span v-html="loc.p_egg_shop_free_with_vip"></span><icon class="egg-store-item-type shadow-filter" name="ico-vip"></icon></button>
				<!-- </div> -->
			</div>
		</div>
		<!-- <div v-if="item.salePrice" class="sale-desc box_absolute bottom-0">20% EGGSTRA!</div> -->
	</div>
</template>
<script>
    const comp_store_item = {
        template: '#comp-store-item',
        props: ['loc', 'item', 'inStore', 'accountSet', 'isUpgraded', 'isSaleEvent'],
        data() {
            return {
                purchased: false,
                attempt: 0,
            };
        },
        methods: {
            onItemClicked() {
                if (!this.accountSet || !vueApp.accountSettled) {
                    vueApp.hideEggStorePopup();
                    setTimeout(() => {
                        if (this.attempt < 5) {
                            this.onItemClicked(this.item.sku);
                            this.attempt++;
                        } else {
                            vueApp.showGenericPopup('uh_oh', 'error', 'ok');
                        }
                    }, 300);
                    vueApp.pleaseWaitPopup();
                    return;
                }
				
				vueApp.hidePopupEggStoreSingle();

                // if (this.$parent.$el.id === 'eggStore') {
                //     this.$parent.$parent.hide();
                // } else {
                //     this.$parent.hide();
                // }

				if (this.item.type === 'item' && !hasValue(this.item.itemId.id) && !Number.isInteger(this.item.itemId)) {
					vueApp.showGenericPopup('uh_oh', 'p_egg_shop_no_item_id', 'ok');
					return;
				}

				if (this.purchased) {
					console.log('Item is owned so lets go see it.');
					vueApp.showItemOnEquipScreen(extern.catalog.findItemById(this.item.itemId.id));
					return;
				}

				
                extern.buyProductForMoney(this.item.sku);


                ga('send', 'event', vueApp.googleAnalytics.cat.purchases, vueApp.googleAnalytics.action.eggShackProductClick, this.item.sku);
            },
            isPurchased() {
                if (this.item.itemId) {
                    return this.purchased = extern.isItemOwned(this.item.itemId);
                }
            },
            onVipClick() {
                // this.$parent.$parent.hide();
                vueApp.showSubStorePopup();
            },
			getItemType() {
				const item = extern.catalog.findItemById(this.item.itemId.id);
				const obj = {};

				if (item.exclusive_for_class !== null) {
					obj.name = getKeyByValue(CharClass, item.exclusive_for_class).toLowerCase();
					obj.isClass = true;
				} else {
					obj.name = getKeyByValue(ItemType, item.item_type_id).toLowerCase();
					obj.isClass = false;
				}

				return obj;
			}
        },
        computed: {
            title() {
                return `${this.item.sku}_title`;
            },
            description() {
                return `${this.item.sku}_desc`;
            },
			saleDesc() {
				if (this.item.salePrice) {
					return `${this.item.sku}_sale_desc`;
				}
			},
            img() {
                if (this.item.type === 'item') {
                    return `img/store/items/${this.item.sku}.gif`;
                }
                return `img/${!this.isSaleEvent ? '' : 'store-black-friday/'}${this.item.sku}.png`;
            },
            itemType() {
                return 'single-egg-store-item-is-' + this.item.type;
            },
			itemCls() {
				return `${this.itemType} ${this.item.sku} ${this.item.type === 'currency' ? '' : ''}`;
			},
            buyBtnText() {
                if (this.purchased) {
                    return this.loc.p_egg_shop_see_item;
                }
                return this.loc.p_buy_item_confirm;
            },
            showItem() {
                if (this.inStore) return this.item.inStore;
                this.isPurchased();
                return true;
            },
            flagTxt() {
                if (this.item.salePrice) {
                    return 'fa-tag';
                }
                return 'fa-gem'
            },
			showSubtitle() {
				if (this.item.type === 'item') return true;
				if (this.isSaleEvent) {
					return false;
				}
				return true;
			},
			hasPurchased() {
				return this.isPurchased();
			},
			price() {
				if (this.item.salePrice) {
					return this.item.salePrice;
				} else {
					return this.item.price;
				}
			},
			subTitleCls() {
				if (this.item.type === 'item') {
					return 'text-center text_brown';
				} else {
					 return 'text_blue5 nospace';
				}
			},
			getItemInfo() {
				if (!this.item.itemId.id) {
					return;
				}
				const item = extern.catalog.findItemById(this.item.itemId.id);

				if (item.exclusive_for_class !== null) {
					return `ico-weapon-${getKeyByValue(CharClass, item.exclusive_for_class).toLowerCase()}`;
				} else {
					return `ico-${getKeyByValue(ItemType, item.item_type_id).toLowerCase()}`;
				}

				// return `img/weapon-icons/ico_weapon_${ItemNames[this.getItemType()]}.svg`;
			}
        },
        watch: {
            accountSet(val) {
				if (val) {
					this.isPurchased();
                }
            },
			isUpgraded(val) {
				if (val) {
					this.isPurchased();
                }
			}
        }
    };
</script>
<script>
var comp_egg_store = {
    template: '#egg-store-template',
    components: {
        'egg-store-item': comp_store_item,
    },
    data() {
        return vueData;
    },
    props: ['loc', 'products', 'saleEvent'],
    
    methods: {
        onItemClicked: function (sku) {
            if (!this.accountSettled) {
                console.log(this.$parent.hide());
                setTimeout(() => {
                    this.onItemClicked(sku)
                }, 300);
                vueApp.pleaseWaitPopup();
                return;
            }
            if (vueApp.$refs.genericPopup.isShowing === true) vueApp.$refs.genericPopup.close();
            this.$parent.hide();
            extern.buyProductForMoney(sku);
            ga('send', 'event', this.googleAnalytics.cat.purchases, this.googleAnalytics.action.eggShackProductClick, sku);
        }
    },
};
</script>
<script>
var comp_equip_screen = {
	template: '#equip-screen-template',
	components: {
		'equipped-slots': comp_equipped_slots,
		'color-select': comp_color_select,
		'item-timer': comp_item_timer,
		'price-tag': comp_price_tag,
		'physical-tag': comp_physical_tag,
		'item-type-selector': comp_item_type_selector,
		'item-grid': comp_item_grid,
		'house-ad-small': comp_house_ad_small,
		'weapon-select-panel': comp_weapon_select_panel,
		'item': comp_item,
		// 'account-panel': comp_account_panel,
		'house-ad-small': comp_house_ad_small,
		'egg-store': comp_egg_store,
	},

	data: function () {
		return vueData;
	},

	equippedItems: {},

	methods: {
		setup: function (itemType) {
			if (!itemType) { itemType = ItemType.Primary; }
			this.updateEquippedItems();
			this.poseEquippedItems();
			if (itemType === 'tagged') {
				this.populateItemGridWithTagged(this.equip.specialItemsTag);
			} else {
				this.populateItemGridWithType(itemType);
			}
			this.selectEquippedItemForType();
		},

		updateEquippedItems: function () {
			this.$options.equippedItems = extern.getEquippedItems();

			this.equip.equippedHat = this.$options.equippedItems[ItemType.Hat];
			this.equip.equippedStamp = this.$options.equippedItems[ItemType.Stamp];
			this.equip.equippedGrenade = this.$options.equippedItems[ItemType.Grenade];
			this.equip.equippedMelee = this.$options.equippedItems[ItemType.Melee];
			this.equip.equippedPrimary = this.$options.equippedItems[ItemType.Primary];
			this.equip.equippedSecondary = this.$options.equippedItems[ItemType.Secondary];
		},

		poseEquippedItems: function () {
			this.posingHat = this.equip.equippedHat;
			this.posingStamp = this.equip.equippedStamp;

			switch (this.equip.showingWeaponType) {
				case ItemType.Primary:
					this.posingWeapon = this.equip.equippedPrimary;
					this.posingGrenade = null;
					this.posingMelee = null;
					break;
				case ItemType.Secondary:
					this.posingWeapon = this.equip.equippedSecondary;
					this.posingGrenade = null;
					this.posingMelee = null;
					break;
				case ItemType.Grenade:
					this.posingGrenade = this.equip.equippedGrenade;
					this.posingMelee = null;
					break;
				case ItemType.Melee:
					this.posingMelee = this.equip.equippedMelee;
					this.posingGrenade = null;
					break;
			}

			this.workItBaby();
		},

		selectEquippedItemForType: function () {

			switch (this.equip.selectedItemType) {
				case ItemType.Hat: this.equip.selectedItem = this.equip.equippedHat; break;
				case ItemType.Stamp: this.equip.selectedItem = this.equip.equippedStamp; break;
				case ItemType.Primary: this.equip.selectedItem = this.equip.equippedPrimary; break;
				case ItemType.Secondary: this.equip.selectedItem = this.equip.equippedSecondary; break;
				case ItemType.Grenade: this.equip.selectedItem = this.equip.equippedGrenade; break;
				case ItemType.Melee: this.equip.selectedItem = this.equip.equippedMelee; break;
			}
		},

		populateItemGridWithType: function (itemType) {
			this.equip.selectedItemType = itemType;
			var items = extern.getItemsOfType(itemType);
			this.populateItemGrid(items);
			
			this.equip.categoryLocKey = 'item_type_{0}{1}'.format(itemType, ((itemType === ItemType.Primary) ? '_' + this.classIdx : ''));
		},

		populateItemGridWithTagged: function (tag, itemType) {
			var items = extern.getTaggedItems(tag, itemType);
			this.populateItemGrid(items);
			this.equip.categoryLocKey = 'item_type_5';
		},

		populateItemGrid: function (items) {
			if (this.currentEquipMode === vueData.equipMode.inventory) {
				items = items.filter(i => extern.isItemOwned(i) || (i.is_available && i.unlock === "default"));
			} else {
				items = items.filter(i => i.is_available && !extern.isItemOwned(i) && (i.unlock === 'purchase' || (i.unlock === 'premium' && i.sku)));
			}

			this.equip.showingItems = items;
		},

		workItBaby: function () {
			extern.poseWithItem(ItemType.Hat, this.posingHat);
			extern.poseWithItem(ItemType.Stamp, this.posingStamp);
			extern.poseWithItem(this.posingWeapon.item_type_id, this.posingWeapon);
			if (this.posingGrenade) extern.poseWithItem(this.posingGrenade.item_type_id, this.posingGrenade);
			if (this.posingMelee) extern.poseWithItem(this.posingMelee.item_type_id, this.posingMelee);
		},

		onBackClick: function () {
			vueApp.showSpinner();
			extern.saveEquipment(() => {
				if (extern.inGame) {
					extern.closeEquipInGame();
				}
				vueApp.hideSpinner();
				this.equip.showingWeaponType = ItemType.Primary;
				this.poseEquippedItems();
			});
		},

		onItemTypeChanged: function (itemType) {
			// if (this.isOnEquipModeFeatured) {
			// 	//this.equip.specialItemsTag
			// 	// this.populateItemGridWithTaggedItemType();
			// 	this.populateItemGridWithTagged(this.equip.specialItemsTag, itemType);
			// 	this.selectFirstItemInShop();
			// 	return;
			// }
			this.switchItemType(itemType);
		},

		switchItemType: function (itemType) {

			// if (this.equip.showingWeaponType == ItemType.Grenade) {
			// 		this.equip.showingWeaponType = null;
			// 		this.posingGrenade = null;
			// }

			// if (this.equip.showingWeaponType == ItemType.Melee) {
			// 	this.equip.showingWeaponType = null;
			// }

			this.equip.showingWeaponType = null;
			this.posingGrenade = null;
			this.posingMelee = null;

			if (itemType !== this.equip.selectedItemType) {
				if (itemType === ItemType.Primary || itemType === ItemType.Secondary || itemType == ItemType.Grenade || itemType == ItemType.Melee) {
					this.equip.showingWeaponType = itemType;
				}

				this.poseEquippedItems();
				this.populateItemGridWithType(itemType);
				if (this.isEquipModeInventory) {
					this.selectEquippedItemForType();
				} else {
					this.selectFirstItemInShop();
				}
			}
			if (!this.isInShop) {
				this.hideItemForSale();
				this.hideItemForSpecial();
			}
			BAWK.play('ui_click');
		},

		onTaggedItemsClicked: function () {
			if (this.equip.selectedItemType === 'tagged') {
				return;
			}
			this.showTaggedItems(this.equip.specialItemsTag);
			this.selectFirstItemInShop();
		},

		onTaggedResetItems() {
			this.showTaggedItems(this.equip.specialItemsTag);
			this.selectFirstItemInShop();	
		},

		onPremiumItemsClicked() {
			if (this.equip.selectedItemType === 'premium') {
				return;
			}
			this.showPremiumItems();
			this.selectFirstItemInShop();
		},

		showPremiumItems() {
			this.equip.selectedItemType = 'premium';
			var items = extern.getPremiumItems();
			this.populateItemGrid(items);
			this.equip.categoryLocKey = 'item_type_7';
		},

		showTaggedItems: function (tag, itemType) {
			this.equip.selectedItemType = 'tagged';

			if ((this.isEquipModeInventory && !this.ownsTaggedItems(this.equip.specialItemsTag)) || extern.openShopOnly) {
				this.currentEquipMode = this.equipMode.shop;
				vueApp.conditionalAnonWarningCall();
				extern.openShopOnly = false;

			}
			
			this.populateItemGridWithTagged(tag);
		},

		showSelectedTagItems(tag) {
			this.equip.selectedItemType = 'tagged';
			// if (this.equip.mode === this.equip.equipMode.inventory) {
			// 	this.equip.mode = this.equip.equipMode.shop;
			// }
			this.populateItemGridWithTagged(tag);
		},

		ownsTaggedItems: function (tag) {
			return extern.getTaggedItems(tag).filter(i => {
					return extern.isItemOwned(i);
				}).length > 0;
		},

		selectFirstItemInShop: function () {
			if (this.isInShop && this.equip.showingItems.length > 0) {
				this.selectItem(this.equip.showingItems[0]);
			}
		},

		resetItemsOnSwitch() {
			if (this.posingMelee || this.posingGrenade) this.equip.showingWeaponType = null;
			this.posingHat = this.equip.equippedHat;
			this.equip.showingItems = [];
			this.posingMelee = null;
			this.posingGrenade = null;
			this.poseEquippedItems();
		},

		switchTo: function (mode, useItemType) {
			if (!this.accountSettled) {
				return;
			}
			// We don't want any lingering possed weapons or items
			this.resetItemsOnSwitch();

			switch (mode) {
				case this.equipMode.shop:
					this.currentEquipMode = this.equipMode.shop;
					this.onSwitchToShopInventory(true);
					// this.selectItem(this.getFeaturedItems[0]);
					break;

				case this.equipMode.inventory:
					if (this.equip.selectedItemType === 'tagged' && this.isOnEquipModeSkins || this.equip.selectedItemType === 'tagged' && this.isOnEquipModeFeatured) {
						if (!this.ownsTaggedItems(this.equip.specialItemsTag)) {
							this.switchItemType(ItemType.Primary);
						}
					}

					this.currentEquipMode = this.equipMode.inventory;
					this.hideItemForSale();
					this.hideItemForSpecial();
					this.poseEquippedItems();
					this.showItemsAfterEquipModeSwitch();
					this.selectEquippedItemForType();
					break;

				case this.equipMode.featured:
					if (this.currentEquipMode === this.equipMode.shop) {
						this.setup();
					}
					this.onTaggedItemsClicked();
					this.onSwitchToShopInventory(true);

					this.currentEquipMode = this.equipMode.featured;
					break;

				case this.equipMode.skins:

					this.setup(useItemType);
					this.currentEquipMode = this.equipMode.skins;
					this.showItemsAfterEquipModeSwitch();
					vueApp.conditionalAnonWarningCall();
					
					break;
			
				default:
					break;
			}

			this.selectFirstItemInShop();

			BAWK.play('ui_toggletab');
			// vueApp.histPushState({game: this.screens.equip}, 'Shellshockers equipment shop', '?equip=shop');
		},

		showItemsAfterEquipModeSwitch: function () {
			if (this.equip.selectedItemType === 'tagged') {
				this.showTaggedItems(this.equip.specialItemsTag)
			} else if (this.equip.selectedItemType === 'premium') {
				this.showPremiumItems();
			} else {
				this.populateItemGridWithType(this.equip.selectedItemType);
			}
		},

		onEquippedTypeSelected: function (itemType) {
			this.equip.selectedItemType = itemType;
			if (
				this.equip.selectedItemType === ItemType.Primary ||
				this.equip.selectedItemType === ItemType.Secondary ||
				this.equip.selectedItemType === ItemType.Grenade ||
				this.equip.selectedItemType === ItemType.Melee
			) {
				this.equip.showingWeaponType = itemType;
			}
			this.switchTo(this.equipMode.inventory);
		},

		onChangedClass: function () {

			this.hideItemForSale();
			this.hideItemForSpecial();

			if (this.posingGrenade) {
				this.posingGrenade = null;
				this.onEquippedTypeSelected(ItemType.Primary);
			}

			if (this.posingMelee) {
				this.posingMelee = null;
				this.onEquippedTypeSelected(ItemType.Primary);
			}

			if (this.equip.selectedItemType !== ItemType.Primary) {
				this.onEquippedTypeSelected(ItemType.Primary);
			}

			this.updateEquippedItems();
			this.poseEquippedItems();
			this.populateItemGridWithType(this.equip.selectedItemType);
			if (extern.inGame && this.showScreen !== this.screens.equip) {
				this.equip.showingWeaponType = ItemType.Primary;
				this.poseEquippedItems();
				extern.closeEquipInGame(true);
			}

		},

		autoSelectItem: function (item) {
			if (extern.isItemOwned(item)) {
				this.switchTo(this.equipMode.inventory);
			} else {
				this.switchTo(this.equipMode.shop);
			}
			if (item.exclusive_for_class) {
				extern.changeClass(item.exclusive_for_class);
				this.onChangedClass();
			} else {
				this.switchItemType(item.item_type_id);
			}
			this.selectItem(item);
		},

		onItemSelected: function (item) {
			if (this.$refs.buyItemPopup.isShowing) return;
			this.selectItem(item);
		},

		isPremItemInStore(item) {
			let hasItem = false;
			const isItem = this.premiumShopItems.find(i => i.isActive && i.itemId.id === item.id);
			if (hasValue(isItem)) {
				hasItem = true;
			}
			return hasItem;
		},

		selectItem: function (item, isFromShop) {
			if (!hasValue(item)) return;
			var selectingSame = hasValue(this.equip.selectedItem) && this.equip.selectedItem.id === item.id;
			var selectedId = selectingSame ? this.equip.selectedItem.id : null;
			var isWeapon = (item.item_type_id === ItemType.Primary || item.item_type_id === ItemType.Secondary);

			if (selectingSame) {
				if (this.isInShop) {
					// Revert to equipped weapon
					item = this.$options[this.equip.selectedItem.item_type_id];
				} else {
					// Take off hat or stamp
					if (!isWeapon) {
						item = null;
						extern.removeItemType(this.equip.selectedItem.item_type_id);
					}
				}
			}

			if (this.isInShop) {
				if ( item && item.hasOwnProperty('unlock') && item.unlock === 'premium' && !this.isPremItemInStore(item)) {
					// Revert to equipped weapon
					item = this.$options[this.equip.selectedItem.item_type_id];
				}
			}

			// Take off any items being tried on
			this.poseEquippedItems();

			this.equip.selectedItem = item;

			extern.tryEquipItem(item);
			this.updateEquippedItems();

			if (hasValue(item)) {
				this.poseWithItem(item);

				if (this.isInShop) {
					switch (item.unlock) {
						case "physical":
							console.log('purchasing physical item');
							
							if ( !selectingSame ) {
								this.offerItemForSpecial(item)
								this.hideItemForSale();
							} else {
								this.hideItemForSpecial();
							}
							// this.$refs.physicalUnlockPopup.toggle();
							break;
						case "purchase":
						case "premium":
							if (!selectingSame) {
								this.offerItemForSale(item);
								this.hideItemForSpecial();
							} else {
								this.hideItemForSale();
							}
						break;
					}
				}
			} else {
				this.poseEquippedItems();
				this.hideItemForSale();
				this.hideItemForSpecial();

			}
		},

		poseWithItem: function (item) {
			switch (item.item_type_id) {
				case ItemType.Hat: this.posingHat = item; break;
				case ItemType.Stamp: this.posingStamp = item; break;
				case ItemType.Grenade: this.posingGrenade = item; break;
				case ItemType.Melee: this.posingMelee = item; break;
				case ItemType.Primary:
				case ItemType.Secondary: this.posingWeapon = item; break;
			}

			this.workItBaby();
		},

		getButtonToggleClass: function (equipMode) {
			return (equipMode === this.currentEquipMode) ? 'btn_toggleon' : 'btn_toggleoff';
		},

		offerItemForSpecial: function(item) {
			return this.equip.physicalUnlockPopup.item = item;
		},

		hideItemForSpecial: function() {
			this.equip.physicalUnlockPopup.item = null;
		},

		offerItemForSale: function (item) {
			this.equip.buyingItem = item;
		},

		hideItemForSale: function () {
			this.equip.buyingItem = null;
			// this.equip.physicalUnlockPopup.item = null
		},

		onBuyItemClicked: function () {
			// If item is buying item show buyItemPopup or show physicalUnlockPopup
			if (this.equip.selectedItem && (this.isEquipModeShop || this.isOnEquipModeFeatured)) {
				this.equip.buyingItem = this.equip.selectedItem;
			}
			this.equip.buyingItem ? this.$refs.buyItemPopup.toggle() : this.$refs.physicalUnlockPopup.toggle();
			this.equip.buyingItem ? extern.renderItemToCanvas(this.equip.buyingItem, this.$refs.buyItemCanvas) : null;
			BAWK.play('ui_popupopen');
		},

		onBuyItemConfirm: function () {
			if (this.equip.buyingItem.unlock === 'premium') {
				extern.buyProductForMoney(this.equip.buyingItem.sku);
				return;
			} else {
				extern.api_buy(this.equip.buyingItem, this.boughtItemSuccess, this.boughtItemFailed);
			}
			BAWK.play('ui_playconfirm');
		},

		boughtItemSuccess: function () {
			this.equip.selectedItem = this.equip.buyingItem;
			ga('send', 'event', {
				eventCategory: this.googleAnalytics.cat.itemShop,
				eventAction: this.googleAnalytics.action.shopItemPopupBuy,
				eventLabel: this.equip.buyingItem.name,
				eventValue: this.equip.selectedItem.price
			});
			var itemType = this.equip.selectedItem.item_type_id;
			if (itemType === ItemType.Primary || itemType === ItemType.Secondary || itemType == ItemType.Grenade || itemType == ItemType.Melee) {
				this.equip.showingWeaponType = itemType;
			}
			this.hideItemForSale();
			this.setup(this.equip.selectedItemType);
			this.updateEquippedItems();
			this.poseEquippedItems();
			this.selectEquippedItemForType();
		},

		boughtItemFailed: function () {
			vueApp.showGenericPopup('p_buy_error_title', 'p_buy_error_content', 'ok');
			BAWK.play('ui_reset');
		},

		onRedeemClick: function () {
			this.$refs.redeemCodePopup.code = '';
			this.$refs.redeemCodePopup.toggle();
			BAWK.play('ui_popupopen');
		},

		onRedeemCodeConfirm: function () {
			if (this.equip.redeemCodePopup.code.toUpperCase() === 'D3LL0RKC1R') {
				this.giveStuffPopup.eggOrg = false;
				this.giveStuffPopup.rickroll = true;
				vueApp.showGiveStuffPopup('p_give_stuff_title');
			} else {
				this.giveStuffPopup.rickroll = false;
				this.giveStuffPopup.eggOrg = false;
				extern.api_redeem(this.equip.redeemCodePopup.code, this.redeemCodeSuccess, this.redeemCodeFailed);
			}
			BAWK.play('ui_playconfirm');
		},

		redeemCodeSuccess: function (eggs, items) {
			this.populateItemGridWithType(this.equip.selectedItemType);
			

			this.giveStuffPopup.eggs = eggs;
			this.giveStuffPopup.items = items;
			vueApp.showGiveStuffPopup('p_give_stuff_title', eggs, items);

			let itemString = '';
			this.giveStuffPopup.items.forEach(item => itemString += item.name);

			ga('send', 'event', {
				eventCategory: this.googleAnalytics.cat.redeem,
				eventAction: this.googleAnalytics.action.redeemed,
				eventLabel: `${itemString ? itemString : ''} ${this.giveStuffPopup.eggs ? this.giveStuffPopup.eggs + 'eggs' : ''}`
			});
		},

		redeemCodeFailed: function () {
			vueApp.showGenericPopup('p_redeem_error_title', 'p_redeem_error_content', 'ok');
			BAWK.play('ui_reset');
		},

		onPhysicalUnlockConfirm: function () {
			window.open(this.equip.physicalUnlockPopup.item.item_data.physicalItemStoreURL, '_blank');
		},

		onColorChanged: function (colorIdx) {
			this.equip.colorIdx = colorIdx;
			extern.setShellColor(this.equip.colorIdx);
			BAWK.play('ui_onchange');
		},

		onSwitchToSkinsClicked: function () {
			this.switchTo(this.equipMode.skins, this.equip.selectedItemType);
		},

		onSwitchToShopInventory(selectFirstItem) {
			if (this.ui.premiumFeaturedTag && this.equip.showUnVaultedItems.length === 0) {
				this.equip.showUnVaultedItems = extern.getTaggedItems(this.ui.premiumFeaturedTag);
			}

			// if (selectFirstItem) {
			// 	this.selectItem(this.equip.showUnVaultedItems[0], true);
			// }
		}
	},

	computed: {
		showShop() {
			return this.showScreen === this.screens.equip;
		},

		isOnEquipModeSkins() {
			return this.currentEquipMode === this.equipMode.skins;
		},

		isOnEquipModeFeatured() {
			return this.currentEquipMode === this.equipMode.featured;
		},

		isEquipModeInventory() {
			return this.currentEquipMode === this.equipMode.inventory;
		},

		isEquipModeShop() {
			return this.currentEquipMode === this.equipMode.shop;
		},

		isInShop: function () {
			return (this.isOnEquipModeSkins || this.isOnEquipModeFeatured || this.isEquipModeShop);
		},

		isOnShopInventoryLimited() {
			return (this.isOnEquipModeSkins || this.isEquipModeInventory || this.isOnEquipModeFeatured)
		},

		isOnShopInventory() {
			return (this.isOnEquipModeSkins || this.isEquipModeInventory)
		},

		getGridClass: function () {
			if (this.equip.selectedItemType !== 'tagged') {
				return`item-grid-${getKeyByValue(ItemType, this.equip.selectedItemType).toLowerCase()}`;
			}
		},

		gridCls() {
			return 'box_relative center_h overflow-x-hidden';
		},

		isEggStoreSaleItem() {
            return this.eggStoreItems.some( item => item['salePrice'] !== '');
		},
		isBuyingItemPrem() {
			if (!this.equip.buyingItem) {
				return;
			}
			return this.equip.buyingItem.unlock === 'premium';
		},
		showPurchasesUi() {
			return this.showShop && this.isEquipModeShop;
		},
		showShopUi() {
			return this.showShop && (this.isOnEquipModeSkins || this.isOnEquipModeFeatured || this.isEquipModeShop);
		},
		screenCls() {
			return `screen-${getKeyByValue(this.equipMode, this.currentEquipMode)}`;
		},
		isSelectedInUnVaulted() {
			return this.equip.showUnVaultedItems.some(item => item.id === this.equip.selectedItem.id);
		},

		showPriceTag() {
			return this.equip.buyingItem && (this.isOnEquipModeFeatured || this.isOnEquipModeSkins) && this.equip.showingItems.length > 0
		}
	}
};
</script><script id="game-screen-template" type="text/x-template">
    <div :class="pauseScreenStateClass">
		<div class="pause-screen-header grid-auto-flow-column box_absolute display-grid z-index-1">
			<div v-show="!isPoki && firebaseId && game.isPaused" id="chw-progress-wrapper" class="chw-progress-wrapper box_relative pause-screen-ui">
				<!-- incentivized-mini-game -->
				<img class="box_aboslute chw-progress-img chw-chick" :src="chwChickSrc">
				<div class="chw-progress-bar-wrap ss_button btn-account-status box_relative" :class="progressBarWrapClass" @click="playIncentivizedAd">
					<p class="chw-progress-bar-msg box_aboslute centered nospace text-center fullwidth chw-msg chw-p-msg text-shadow-black-40">
						{{ progressMsg }}
					</p>
					<div class="chw-progress-bar-inner" @click="playIncentivizedAd">
					</div>
				</div>
			</div>
			<!-- #chw-progress-wrapper end -->
			<!-- <div class="f_row f_end_only align-items-center ">
				<div class="account_eggs roundme_sm clickme">
					<img src="img/ico_goldenEgg.png" class="egg_icon">
					<span class="egg_count">{{ eggs }}</span>
				</div>
				<button v-if="game.isPaused" @click="onShareLinkClicked" class="ss_button btn_blue bevel_blue box_relative pause-screen-ui btn-account-w-icon" :title="loc.p_pause_home"><i :class="icon.invite"></i></button>
				<button v-if="game.isPaused" @click="onSettingsClicked" class="ss_button btn_blue bevel_blue box_relative pause-screen-ui btn-account-w-icon" :title="loc.p_pause_home"><i :class="icon.settings"></i></button>
				<button v-if="game.isPaused" @click="onFullscreenClicked" class="ss_button btn_blue bevel_blue box_relative pause-screen-ui btn-account-w-icon" :title="loc.p_pause_home"><i :class="icon.fullscreen"></i></button>
			</div> -->
		</div>
		<div ref="canvasWrap"></div>
		<!-- end .pause-screen-header -->

        <div ref="vipWrapper">
			<div id="chickenBadge" ref="chickenBadge" v-show="game.isPaused && isSubscriber && showScreen === screens.game"><img :src="upgradeBadgeUrl"></div>
		</div>

		<div ref="gameUIWrapper">
			<div ref="gameUiInner" class="paused-game-ui z-index-1 centered_x fullwidth" v-show="showScreen === screens.game">
				<div ref="playerListWrapper" class="player-list-wrapper">
					<div ref="playerContainer" class="player__container" v-show="showScreen === screens.game">
						<div id="playerSlot" class="playerSlot" style="display: none">
							<div>
								<span></span> <!-- Name -->
								<span></span> <!-- Score -->
							</div>
							<div style="display: block;"></div> <!-- Icons -->
						</div>
						<!-- end .playerSlot -->
						<div id="playerList"></div>
					</div>
					<!-- end .player__container -->
				</div>
				<!-- end .player_list_wrapper -->

				<!-- Scope -->
				<div id="scopeBorder">
					<div id="maskleft"></div>
					<div id="maskmiddle"></div>
					<div id="maskright"></div>
				</div>
	
				<!-- Best Streak -->
				<div ref="bestStreakWrapper">
					<div ref="bestStreak" id="best_streak_container"> 
						<h1 v-show="(((doubleEggWeekend || doubleEggWeekendSoon) && !ui.showCornerButtons) || (!doubleEggWeekend && !doubleEggWeekendSoon))" id="bestStreak">x0</h1>
					</div>
				</div>
	
				<div v-show="announcementMessage" id="announcement_message">{{ announcementMessage }}</div>
	
				<div id="shellStreakContainer">
					<h1 id="shellStreakCaption"></h1>
					<h1 id="shellStreakMessage" class="disappear"></h1>
				</div>
	
				<!-- Team Scores -->
				<div id="teamScores">
					<div id="teamScore2" class="teamScore red inactive">
							<div id="teamScoreNum2" class="number">0</div>
							<div class="teamLetter red" style="color: #f00;">R</div>
					</div>
					<div id="teamScore1" class="teamScore blue inactive">
							<div id="teamScoreNum1" class="number">0</div>
							<div class="teamLetter blue" style="color: #0af;">B</div>
					</div>
					<!--<div>
							<img src="img/spatulaIcon.png" style="width: 3em; transform: rotate(60deg)">
					</div>-->
				</div>

				<!-- Capture Icon -->
				<div ref="captureIconWrap" id="captureIconWrap">
					<div ref="captureIconContainer" id="captureIconContainer">
						<div id="captureInsideContainer">
							<div id="captureIconCaption">20M</div>
							<div id="captureRingBackground"></div>
							<div id="captureRingContainer">
								<div id="captureRing"></div>
							</div>
		
							<svg id="captureIcon" viewBox="0 0 53.6 36">
								<use href="img/kotc/crown.svg#crown" />
							</svg>
						</div>
					</div>
				</div>
	
				<!-- Reticle -->
				<div id="reticleContainer">
					<div id="dotReticle"></div>
					
					<div id="crosshairContainer">
						<div id="crosshair0" class="crosshair normal"></div>
						<div id="crosshair1" class="crosshair normal"></div>
						<div id="crosshair2" class="crosshair normal"></div>
						<div id="crosshair3" class="crosshair normal"></div>
					</div>
	
					<div id="shotReticleContainer">
						<div id="shotBracket0" class="shotReticle border normal"></div>
						<div id="shotBracket1" class="shotReticle border normal"></div>
						<div id="shotBracket2" class="shotReticle fill normal"></div>
						<div id="shotBracket3" class="shotReticle fill normal"></div>
					</div>
	
					<div id="readyBrackets">
						<div class="readyBracket"></div>
						<div class="readyBracket"></div>
						<div class="readyBracket"></div>
						<div class="readyBracket"></div>
					</div>
				</div>
	
				<!-- Capture Zone progress -->
				<div id="captureContainer">
					<div class="captureScoreContainer">
						<svg class="captureCrown" viewBox="0 0 53.6 36">
							<use href="img/kotc/crown.svg#crown" fill="var(--ss-team-red-light)" />
						</svg>
	
						<div id="captureScoreRed" class="captureScore">0/5</div>
					</div>
	
					<div id="captureCenter">
						<div id="captureBarContainer">
							<div id="captureBar"></div>
						</div>
						<div id="captureBarText"></div>
					</div>
	
					<div class="captureScoreContainer">
						<svg class="captureCrown" viewBox="0 0 53.6 36">
							<use href="img/kotc/crown.svg#crown" fill="var(--ss-team-blue-light)" />
						</svg>
						<div id="captureScoreBlue" class="captureScore">0/5</div>
					</div>
				</div>
	
				<!-- Big Message Bar -->
				<div id="bigMessageContainer" style="display: none">
					<div id="bigMessageBar"> 
						<div id="bigMessage"></div>
						<div id="bigMessageCaption"></div>
					</div>
				</div>
	
				<!-- Weapon -->
				<div id="weaponBox">
					<div id="grenades">
						<img id="grenade3" class="grenade" src="img/ico_grenadeEmpty.png?v=1"/>
						<img id="grenade2" class="grenade" src="img/ico_grenadeEmpty.png?v=1"/>
						<img id="grenade1" class="grenade" src="img/ico_grenadeEmpty.png?v=1"/>
					</div>
					<h2 id="weaponName"></h2>
					<h2 id="ammo" class="shadow_grey"></h2>
				</div>
	
				<!-- Health -->
				<div id="healthContainer" v-show="!game.isPaused">
					<svg class="healthSvg">
						<circle id="healthBar" class="healthBar" cx="50%" cy="50%" r="2.15em" />
						<circle class="healthYolk" cx="50%" cy="50%" r="1.35em" />
					</svg>
	
					<div id="healthHp">100</div>
				</div>
	
				<!-- Hard Boiled -->
				<div id="hardBoiledContainer">
					<div id="hardBoiledShieldContainer">
						<img class="hardBoiledShield" src="img/hardBoiledEmpty.png">
						<img class="hardBoiledShield" id="hardBoiledShieldFill" src="img/hardBoiledFilled.png">
					</div>
	
					<div id="hardBoiledValue">100</div>
				</div>
	
				<!-- EggBreaker Shellstreak -->
				<div id="eggBreakerContainer" class="off">
					<img id="eggBreakerIcon" src="img/ico_eggBreaker.png" />
					<div id="eggBreakerTimer">15</div>
				</div>
	
				<!-- Spatula -->
				<img id="spatulaPlayer" src="img/spatulaIcon.png" />
	
				<!-- Grenade throw power -->
				<div id="grenadeThrowContainer">
					<div id="grenadeThrow"></div>
				</div>
	
				<!-- Kill -->
				<div id="killBox" class="shadow_grey">
					<!-- <h3>{{ loc.ui_game_youkilled }}</h3> -->
					<div v-html="killedMessage"></div>
					<!-- <h2 id="KILLED_NAME"></h2> -->
					<h3 id="KILL_STREAK"></h3>
				</div>
	
				<!-- Death -->
				<div id="deathBox">
					<div v-html="killedByMessage"></div>
				</div>
	
				<!-- Game messages -->
				<div id="gameMessage"></div>

				<!-- Kill ticker -->
				<div ref="killTickerWrapper">
					<div ref="killTicker" id="killTicker" class="chat"></div>
				</div>

				<!-- Spectator controls -->
				<div id="spectate">
					{{ loc.ui_game_spectating }}
				</div>

				<div ref="spectateWrap" class="spectate-wrapper">
					<button ref="spectateBtn" v-show="!isRespawning && game.isPaused && showScreen === screens.game && delayTheCracking" @click="onSpectateClicked()" class="ss_button btn_blue bevel_blue btn_sm pause-screen-btn-spectate" :disabled="isRespawning" :title="loc.p_pause_spectate">
						<i class="fas fa-eye fa-2x"></i>
					</button>
				</div>
			</div>
		</div>
		<!-- ref gameUIWrapper -->
		
		<div ref="chatWrapper" class="chat-wrapper pause-ui-element box_absolute roundme_lg">
			<div ref="chatContainer" class="chat-container">
				<div id="chatOut" class="chat roundme_sm"></div>
				<input id="chatIn" class="chat roundme_sm" maxlength=64 tabindex=-1 v-bind:placeholder="loc.ingame_press_tab_to_exit" onkeydown="extern.onChatKeyDown(event)" onclick="extern.startChat(event)" onblur="extern.stopChat(event)"></input>
			</div>
		</div>
        <!-- Chat -->

		<!-- Ingame UI Stuff --> 
		<div id="inGameUI" class="roundme_lg">
			<div id="serverAndMapInfo"></div>
			<div id="readouts">
               <h5 class="nospace title">{{ loc.ui_game_fps }}</h5>
				<p id="FPS" class="name"></p>
                <h5 class="nospace title">{{ loc.ui_game_ping }}</h5>
				<p id="ping" class="name"></p>
			</div>
		</div>

        <!-- Popup: Mute/Boot Player -->
        <small-popup id="playerActionsPopup" ref="playerActionsPopup" @popup-cancel="onPlayerActionsCancel" @popup-closed="onPlayerActionsCancel" :hide-confirm="true" @popup-opened="sharedPopupOpened" @popup-closed="sharedPopupClosed">
            <template slot="header">{{ playerActionsPopup.playerName }}</template>
            <template slot="content">
                <div v-if="playerActionsPopup.vipMember" class="vip-member-wraper display-grid align-items-center grid-column-1-2 ss_marginbottom_xl">
                    <figure class="player-action-vip-img center_h">
                        <img src="img/vip-club/vip-club-popup-emblem.png" alt="Vip member icon" class="center_h">
                    </figure>
                    <div>
                        <h6 class="roundme_sm shadow_blue4 ss_margintop ss_marginbottom">{{loc.ui_game_playeractions_vip_member}}</h6>
                        <button v-if="!isSubscriber" class="ss_button btn_pink bevel_pink fullwidth" @click="openVipPopup">{{loc.ui_game_playeractions_join_vip}}</button>
                    </div>
                </div>
                <!-- .vip-member-wraper -->
                <p>{{ loc.ui_game_playeractions_header }}</p>
                <button v-if="playerActionsPopup.social" class="ss_button btn_medium btn_yolk bevel_yolk fullwidth" @click="onClickCreator(playerActionsPopup.social.url)"><i class="fab" :class="playerSocial"></i> {{loc.ui_game_playeractions_creator}}</button>
                <h4 class="ss_button btn_medium btn_blue bevel_blue" v-on:click="onMuteClicked">{{ muteButtonLabel }}</h4>
                <h4 class="ss_button btn_medium btn_yolk bevel_yolk" v-if="playerActionsPopup.isGameOwner" v-on:click="onBootClicked">{{ loc.ui_game_playeractions_boot }}</h4>
            </template>
            <template slot="cancel">{{ loc.cancel }}</template>
        </small-popup>

		<!-- Popup: Switch Team -->
		<small-popup id="switchTeamPopup" ref="switchTeamPopup" @popup-cancel="onSwitchTeamCancel" :overlay-close="false" @popup-closed="onSwitchTeamCancel" @popup-confirm="onSwitchTeamConfirm" @popup-opened="sharedPopupOpened" @popup-closed="sharedPopupClosed">
			<template slot="header">{{ loc.p_switch_team_title }}</template>
			<template slot="content">
                <h4 class="roundme_sm" :class="newTeamColorCss">{{ newTeamName }} <i class="fa fa-flag"></i></h4>
				<p>{{ loc.p_switch_team_text }}</p>
			</template>
			<template slot="cancel">{{ loc.no }}</template>
			<template slot="confirm">{{ loc.yes }}</template>
		</small-popup>

		<!-- Popup: Share Link -->
		<small-popup id="shareLinkPopup" ref="shareLinkPopup" :popup-model="game.shareLinkPopup" @popup-confirm="onShareLinkConfirm" @popup-closed="onShareLinkClosed" @popup-opened="sharedPopupOpened" @popup-closed="sharedPopupClosed">
			<template slot="header">{{ loc.p_sharelink_title }}</template>
			<template slot="content">
				<p>{{ loc.p_sharelink_text }}</p>
				<p><input ref="shareLinkUrl" type="text" class="ss_field ss_margintop ss_marginbottom fullwidth" v-model="game.shareLinkPopup.url" @focus="$event.target.select()" @popup-opened="sharedPopupOpened" @popup-closed="sharedPopupClosed"></p>
			</template>
			<template slot="cancel">{{ loc.close }}</template>
			<template slot="confirm">{{ loc.p_sharelink_copylink }}</template>
		</small-popup>

		<!-- Popup: Pause -->
		<div ref="pausePopupWrap">
			<div ref="pausePopup" class="pause-container centered">
				<div class="pause-screen-wrapper" :class="pauseScreenWrapGrid">
					<div ref="pauseContainer" class="pause-popup--container box_relative roundme_lg display-grid gap-1">
						<div class="pause-ad-wrap display-grid gap-1 center_h align-items-center">
							<div>
								<div class="pause-screen-content pause-bg roundme_md box_relative center_h">
									<section id="btn_horizontal" class="pause-game-weapon-select pause-popup--btn-group">
										<header class="weapon-select weapon-select--title">
											<h1 class="shadow_bluebig4 ss_marginbottom_lg">{{loc.p_weapon_title}}</h1>
										</header>
										<weapon-select-panel ref="weaponSelect" id="weapon_select" :loc="loc" :current-class="classIdx":account-settled="true" :play-clicked="false" @changed-class="pauseWeaponSelect" :current-screen="showScreen" :screens="screens"></weapon-select-panel>
									</section>
								</div>
								<!-- end .pause-screen-content -->
								<div class="pause-screen-play-btn center_h text-center display-grid grid-auto-flow-column ss_margintop gap-1">
									<button v-if="delayTheCracking" v-show="isTeamGame" @click="onSwitchTeamClicked" class="ss_button btn_big btn_team_switch btn-respawn" :class="teamColorCss">
										<div class="display-grid grid-column-1-2 align-items-center">
											<i class="fa fa-flag fa-2x"></i> <div v-html="teamName" class="team-switch-text text-left"></div>
										</div>
									</button>
									<button @click="onPlayClicked()" class="ss_button btn_big btn-respawn" :class="playBtnColor" :disabled="isRespawning"><i v-if="delayTheCracking" v-show="!isRespawning" class="fa fa-play"></i>{{ playBtnText }} <span style="display: inline-block; font-size: .4em">{{ playBtnAdBlockerText }}</span></button>
								</div>
							</div>
							<div v-if="!isSubscriber" class="respawn-container respawn-two" v-show="game.isPaused">
								<display-ad :hidden="hideAds" id="shellshockers_respawn_banner_2_ad" ref="respawnTwoDisplayAd" class="pauseFiller" :ignoreSize="false" :adUnit="displayAd.adUnit.respawnTwo" adSize="300x250"></display-ad>
							</div>
							<!-- .respawn-two smaller-->
						</div>

						<!-- wrapper -->
						<div v-if="!isSubscriber" class="respawn-container respawn-one" v-show="game.isPaused">
							<display-ad :hidden="hideAds" id="shellshockers_respawn_banner-new_ad" ref="respawnDisplayAd" class="pauseFiller" :ignoreSize="false" :adUnit="displayAd.adUnit.respawn" adSize="728x90"></display-ad>
						</div>
						<!-- .respawn-one -->
					</div>
					<!-- end .pause-popup--container -->
				</div>
				<!-- end .pause-screen-wrapper -->
			</div>
		</div>
	</div>
</script>


<script>
var comp_game_screen = {
    template: '#game-screen-template',
    components: {
        'account-panel': comp_account_panel,
        'weapon-select-panel': comp_weapon_select_panel,
		'weapon-select-panel': comp_weapon_select_panel,
    },
    props: ['kname', 'kdname'],
	data: function () {
		return vueData;
    },
    created: function () {
        this.isPoki = pokiActive;
    },
    methods: {
		pauseScreenPlayerListOverflowCheck() {
			if (this.$refs.playerContainer.clientHeight < this.$refs.playerContainer.scrollHeight) {
				this.ui.playerListOverflow = true;
			} else {
				this.ui.playerListOverflow = false;
			}
		},
		windowResize() {
			this.pauseScreenPlayerListOverflowCheck();
			window.addEventListener("resize", this.pauseScreenPlayerListOverflowCheck);
		},
        placeBannerAdTagForGame: function (tagEl) {
            this.$refs.pauseAdPlacement.appendChild(tagEl);
        },
		sharedPopupOpened(id) {
			this.game.openPopupId = id ? id : '';
		},

		sharedPopupClosed(id) {
		},

        showGameMenu: function () {

            // this.game.tipIdx = this.loc.tip_ofthe_day
            // ? Math.randomInt(0, this.loc.tip_ofthe_day.length)
            // : 0;

            this.game.gameType = extern.gameType;
            vueApp.showRespawnDisplayAd();
            setTimeout(() => vueApp.disableRespawnButton(false), 500);
            vueData.ui.showCornerButtons = true;
			vueApp.gameUiAddClassForNoScroll();

            addEventListener('gamepadbuttondown', this.onControllerButton);
            this.crazyAdsRespawn();
			extern.chicknWinnerOnPause();
			vueApp.hideShareLinkPopup();

			this.pauseUi();
			
			vueApp.setPause(true);
		
			setTimeout(() => {
				this.windowResize();
			}, 300);
        },

        delayGameMenuPlayButtons() {
            setTimeout(() => {
                this.delayTheCracking = true;
            }, 3000);
        },

        hideGameMenu: function () {

            if (!extern.inGame)  {
                return;
            }
            if (crazyGamesActive) crazysdk.clearAllBanners(); // Per CG's request
            vueApp.gameUiAddClassForNoScroll();

            removeEventListener('gamepadbuttondown', this.onControllerButton);
			removeEventListener('resize', this.pauseScreenPlayerListOverflowCheck);
        },

        onLeaveGameConfirm: function () {
			if (this.showScreen === this.screens.equip) {
				vueApp.onBackClick();
			}
			this.resetUi();
            this.leaveGame();
            this.delayTheCracking = false;
        },

        onLeaveGameCancel: function () {
            this.showGameMenu();
        },

        leaveGame: function () {
            // clientGame.js manipulates chickenBadge element directly to hide/show it
			removeEventListener('resize', this.pauseScreenPlayerListOverflowCheck);
            vueApp.disablePlayButton(false);
            this.$refs.chickenBadge.style.display = 'none';
            document.body.style.overflow = 'visible';
            window.scrollY = 0;
            this.hidePopupsIfGameCloses();
            extern.leaveGame(this.afterLeftGame);
            vueData.ui.showCornerButtons = true;
            // OneSignal elements are not part of the Vue app
            var oneSignalBell = document.getElementById('onesignal-bell-container');
            if (oneSignalBell) {
                oneSignalBell.style.display = 'none';
            }

        },

        hidePopupsIfGameCloses: function() {
            const gamePopups = vueApp.$refs.gameScreen.$children;
            if (Array.isArray(gamePopups)) {
                gamePopups.forEach( gamePopup => {;
                    if ( gamePopup.isShowing === true && gamePopup.$el.id !== 'pausePopup' ) {
                        gamePopup.close();
                        console.log(`Closing ${gamePopup.$el.id}`);
                    }
                });
            }
        },

        afterLeftGame: function () {
			vueApp.showSpinner();
			setTimeout(() => {
				extern.resize();
				vueApp.hideSpinner();
			}, 200);
			vueApp.showTitleScreenAd();
		    vueApp.switchToHomeUi();
        },

        onHelpClicked: function () {
            // this.hideGameMenu();
            vueApp.showHelpPopup();
            BAWK.play('ui_popupopen');
        },

        onShareLinkClicked: function () {
            extern.inviteFriends();
            BAWK.play('ui_popupopen');
        },

        onSettingsClicked: function () {
            // this.hideGameMenu();
            vueApp.showSettingsPopup();
            BAWK.play('ui_popupopen');
        },

        onShareLinkConfirm: function () {
            extern.copyFriendCode(this.$refs.shareLinkUrl);
        },

        onShareLinkClosed: function () {
			if (extern.inGame) {
				vueApp.showRespawnDisplayAd();
			}
            this.showGameMenu();
        },

        onEquipClicked: function () {
            this.game.pauseScreen.wasGameInventoryOpen = true;
            this.game.pauseScreen.classChanged = false;
            this.gaSend('inventory');
            vueApp.switchToEquipUi(this.equipMode.inventory);
            BAWK.play('ui_equip');
        },

        onSwitchTeamClicked: function () {
            // this.hideGameMenu();
            BAWK.play('ui_popupopen');
            this.$refs.switchTeamPopup.show();
        },

        onSwitchTeamCancel: function () {
            this.showGameMenu();
        },

        onSwitchTeamConfirm: function () {
            extern.switchTeam();
        },

        onControllerButton: function (e) {
            switch (e.detail) {
                case '9':
                    if (document.hasFocus() && !this.isRespawning && this.delayTheCracking) {
                        this.onPlayClicked();
                    }
                    break;

                case '4':
                case '6':
                case '14':
                    vueData.classIdx = Math.max(0, vueData.classIdx - 1);
                    this.$refs.weaponSelect.selectClass(vueData.classIdx);
                    break;

                case '5':
                case '7':
                case '15':
                    vueData.classIdx = Math.min(6, vueData.classIdx + 1);
                    this.$refs.weaponSelect.selectClass(vueData.classIdx);
                    break;
            }
        },

        gaSendOnClassChange() {
            if (!this.game.pauseScreen.wasGameInventoryOpen && this.game.pauseScreen.classChanged) {
                ga('send', 'event', 'respawn-popup', 'classClick', Object.keys(CharClass).find(key => CharClass[key] === vueApp.classIdx));
            }

            // reset for good measure
            this.game.pauseScreen.wasGameInventoryOpen = false;
            this.game.pauseScreen.classChanged = false;
        },

		onPlayClicked() {
			if ((!this.delayTheCracking && !this.isRespawning) || (this.delayTheCracking && this.isRespawning)) {
				return;
			}

            vueApp.disableRespawnButton(true);
            vueData.ui.showCornerButtons = false;
            extern.respawn();
            BAWK.play('ui_playconfirm');
            this.gaSendOnClassChange();
		},

        onSpectateClicked: function () {
            this.gaSend('spectate');
            this.hideGameMenu();
            vueData.ui.showCornerButtons = false;
            extern.enterSpectatorMode();
            BAWK.play('ui_playconfirm');
        },

        showPlayerActionsPopup: function () {
            // this.hideGameMenu();
            this.$refs.playerActionsPopup.show();
        },

        onPlayerActionsCancel: function () {
            this.showGameMenu();
        },

        onMuteClicked: function () {
            this.$refs.playerActionsPopup.hide();
            this.playerActionsPopup.muteFunc();
        },

        onBootClicked: function () {
            this.$refs.playerActionsPopup.hide();
            this.playerActionsPopup.bootFunc();
        },

        resizeBannerAdTagForGame: function() {
            const pauseAdPlacement = document.getElementById('pauseAdPlacement');
            const rect = document.getElementById('pausePopup').getBoundingClientRect();

            pauseAdPlacement.style.top = (rect.height).toString() + 'px';
        },
        earnInGameReward() {
            // this.hideGameMenu();
            vueApp.setDarkOverlay(true);
            this.pokiRewardReady = false;
             this.isPokiNewRewardTimer = false;
            extern.api_inGameReward(this.inGameRewardSuccessCallback, this.inGameRewardFailedCallback, this.rewardReachedDailyLimits);
            extern.setVolume(0);
        },
        inGameRewardSuccessCallback() {
            extern.pokiRewardedBreak(this.inGameRewardIsGranted, this.inGameRewardFailedCallback);
        },
        inGameRewardIsGranted(rewardValue) {
            console.log('inGameRewardSuccessCallback');
            this.isPokiNewRewardTimer = true;
            vueApp.showGiveStuffPopup('reward_title', rewardValue, '');
            ga('send', 'event', 'Poki', 'Rewarded Video', 'Reward Success', this.pokiRewNum);
            this.pokiRewNum ++;
        },
        inGameRewardFailedCallback() {
            vueApp.showGenericPopup('p_redeem_error_title', 'in_game_reward_try_again', 'ok');
            this.isPokiNewRewardTimer = false;
            ga('send', 'event', 'Poki', 'Rewarded Video', 'Failed');
        },
        rewardReachedDailyLimits() {
            vueApp.showGenericPopup('in_game_reward_title', 'in_game_reward_try_again', 'ok');
            this.isPokiNewRewardTimer = false;
            localStore.setItem('inGameRewardLimitDate',  Date.now());
            extern.setVolume();
            ga('send', 'event', 'Poki', 'Rewarded Video', 'Reached Daily Limit');
        },

        pokiTimers(value, milliseconds) {
            let pokiSetTimer;

            if (value === false) {
                clearTimeout(pokiSetTimer);
                console.log('cancelTimer');
                return;
            }
            pokiSetTimer = setTimeout(() => this.pokiRewardReady = true, milliseconds);
        },

        pauseWeaponSelect() {
            this.game.pauseScreen.wasGameInventoryOpen = false;
            this.game.pauseScreen.classChanged = true;
            vueApp.$refs.equipScreen.onChangedClass();
        },
        songHasChanged() {
            setTimeout(() => this.songChanged = false , 2000);
        },

        // So crazy games requires an array for multiple display ad calls on the same screen
        crazyAdsRespawn() {
            if (!crazyGamesActive && !testCrazy) return;

            // Removing delay per CG's request. Refresh rate is restricted server-side
            /*if (this.cGrespawnBannerTimeout) return;
            this.cGrespawnBannerTimeout = setTimeout(() => {
                clearTimeout(this.cGrespawnBannerTimeout);
                this.cGrespawnBannerTimeout = null;
            }, 20000);*/

            this.cGrespawnBannerErrors = 0;

            crazysdk.requestBanner([{
                    containerId: 'shellshockers_respawn_banner-new_ad',
                    size: '728x90',
                },{
                    containerId: 'shellshockers_respawn_banner_2_ad',
                    size: '300x250',
                }]);
        },

        onClickCreator(url) {
            window.open(url, )
        },

        openVipPopup() {
            this.$refs.playerActionsPopup.hide();
            vueApp.showSubStorePopup();
        },
		gaSend(label) {
			if (!label) return;
            ga('send', 'event', 'respawn-popup', 'click', label);
		},
		playIncentivizedAd(e) {
			if (!this.chicknWinnerReady || this.hasChwPlayClicked || this.isChicknWinnerError) {
				e.preventDefault();
				return;
			}
			vueApp.disableRespawnButton(true);
			this.hasChwPlayClicked = true;
			vueApp.loadNuggetVideo();
			vueApp.chicknWinnerNotReady();
			ga('send', 'event', 'Chickn Winner', 'Free eggs btn', 'click-in-game');

		},
		onEggShopClicked() {
			vueApp.switchToEquipUi(this.equipMode.shop);
		},
		onFullscreenClicked() {
			extern.toggleFullscreen();
		},
		pauseUi() {
			vueApp.$refs.gameCanvas.appendChild(this.$refs.gameUiInner);
			vueApp.$refs.gameCanvas.appendChild(this.$refs.pausePopup);
		},
		resetUi() {
			this.$refs.pausePopupWrap.appendChild(this.$refs.pausePopup);
			this.$refs.gameUIWrapper.appendChild(this.$refs.gameUiInner);
		}
    },

    computed: {
        isRespawning: function () {
            return this.game.respawnTime > 0;
        },

        isTeamGame: function () {
            // Would be better to use the same enum as the client game code
            return this.game.gameType !== 0;
        },

        teamColorCss: function () {
            return this.game.team === this.ui.team.blue ? 'blueTeam btn_red bevel_red' : 'redTeam btn_blue bevel_blue';
        },

        teamName: function () {
            return this.game.team === this.ui.team.blue ? this.loc.p_pause_joinred : this.loc.p_pause_joinblue;
        },

        newTeamColorCss: function () {
            return this.game.team === this.ui.team.blue ? 'redTeam btn_red' : 'blueTeam btn_blue';
        },

        newTeamName: function () {
            return this.game.team === this.ui.team.blue ? this.loc.team_red : this.loc.team_blue;
        },

        muteButtonLabel: function () {
            return this.playerActionsPopup.muted ? this.loc.ui_game_playeractions_unmute : this.loc.ui_game_playeractions_mute;
        },
        showIngameWidget() {
            if (!this.game.isPaused && this.songChanged) {
                this.songHasChanged();
                return true;
            }
            return false;
        },
        showMusicWidget() {
            return this.showScreen === 2;
        },
		isEggStoreSaleItem() {
            return this.eggStoreItems.some( item => item['salePrice'] !== '' && this.ui.showCornerButtons);
        },
        upgradeBadgeUrl() {
            return 'img/vip-club/vip-club-popup-emblem.png';
        },
        playerSocial() {
            return SOCIALMEDIA[this.playerActionsPopup.social.id];
        },
		progressMsg() {
			if (this.isChicknWinnerError) {
				return this.loc.chw_error_text;
			}
			if (this.chicknWinnerDailyLimitReached && this.chicknWinnerCounter > 0) {
				return this.loc.chw_daily_limit_msg;
			}
			if (this.chicknWinnerReady) {
				if (this.chicknWinnerCounter === 0) {
					return this.loc.chw_ready_msg;
				} else {
					return this.loc.chw_cooldown_msg;
				}
			} else {
				return this.loc.chw_free_eggs_coming;
			}
		},
		progressBarWrapClass() {
			if (this.isChicknWinnerError) {
				return 'chw-progress-bar-wrap-error';
			}

			if (this.chicknWinnerReady) {
				return 'chw-progress-bar-wrap-complete';
			}
		},
		playAdText() {
			if (this.chicknWinnerReady && this.chicknWinnerCounter === 0) {
				return this.loc.chw_btn_free_reward;
			} else {
				return this.loc.chw_btn_watch_ad;
			}
		},
		chwShowCountdown() {
			if (this.chicknWinnerDailyLimitReached || this.isChicknWinnerError) {
				return 'hideme';
			} else {
				if (this.chicknWinnerReady) {
					return 'hideme';
				} else {
					return 'display-inline';
				}
			}
		},
		chwChickSrc() {
			if (this.chicknWinnerDailyLimitReached || this.isChicknWinnerError) {
				return 'img/chicken-nugget/chickLoop_daily_limit.svg';
			} else {
				if (!this.chicknWinnerReady) {
					return 'img/chicken-nugget/chickLoop_sleep.svg';
				} else {
					return 'img/chicken-nugget/chickLoop_speak.svg';
				}
			}
		},

		playBtnColor() {
			if (!this.delayTheCracking && !this.isRespawning || this.delayTheCracking && this.isRespawning) {
				return 'btn_red bevel_red';
			} else {
				return 'ss_button btn_yolk bevel_yolk';
			}
		},

		playBtnText() {
			if (!this.delayTheCracking && !this.isRespawning) {
				return this.loc.ui_game_get_ready;
			} else if (this.delayTheCracking && this.isRespawning) {
				if (this.game.respawnTime > 5) {
					return this.game.respawnTime - 5;
				} else {
					return this.game.respawnTime
				}
			} else {
				return this.loc.p_pause_play;
			}
		},

		playBtnAdBlockerText() {
			if (this.delayTheCracking && this.isRespawning && extern.adBlocker && this.game.respawnTime <= 5 && !extern.productBlockAds && !this.isPoki) {
				return 'Ad block delay!';
			}
		},
		classGameType() {
			if (this.game.gameType === 0) {
				return 'pause-screen-free-for-all'
			}
		},
		pauseScreenWrapGrid() {
			if (!this.isSubscriber) {
				return 'pause-screen-wrapper-no-vip';
			} else {
				return 'pause-screen-wrapper-is-vip'
			}
		},
		pauseScreenStateClass() {
			if (this.game.isPaused) {
				return `is-paused ${this.game.openPopupId}`
			}
		},
    },

    watch: {
        isPokiGameLoad(value) {
            this.pokiTimers(value, this.videoRewardTimers.initial);
            // this.pokiTimers(value, 300);
        },
        isPokiNewRewardTimer(value) {
            this.pokiTimers(value, this.videoRewardTimers.primary);
            // this.pokiTimers(value, 300);
        },
        kname(val) {
            this.killedByMessage = this.loc['ui_game_killedby'].format(val);
        },
        kdname(val) {
            this.killedMessage = this.loc['ui_game_youkilled'].format(val);
        },
    }

};
</script>
<script id="vip-club-template" type="text/x-template">
    <div class="vip-club fullwidth">

        <div class="vip-club-log-content--outer roundme_sm display-grid align-items-center grid-column-1-2 grid-gap-space-lg">
			<header class="grid-span-2-start-1">
				<!-- <h2><span class="text-orange">V</span>ery <span class="text-orange">I</span>mportant <span class="text-orange">P</span>oultry</h2> -->
				<span class="sr-only">Very Important Poultry</span>
				<img class="vip-club-water-mark display-block center_h" src="img/vip-club/very-important-poultry.png" alt="Very Important Poultry text image">
			</header>
            <div class="vip-club--logo">
                <img src="img/vip-club/vip-club-popup-emblem.png">
            </div>
            <div class="vip-club--content">
                <div class="subs-info">
                    <ul>
                        <li>{{loc.p_chicken_goldfeature1}}</li>
                        <li v-html="loc.p_chicken_goldfeature3"></li>
                        <li>{{loc.p_chicken_goldfeature2}}</li>
                        <li>{{loc.s_popup_feature_premium_items}}</li>
                    </ul>
					<!-- <p class="more">{{loc.home_media_apply_now}}</p> -->
					<button v-on:click="openVipPopup" class="margins_lg ss_button btn_sm btn_vip-button bevel_blue">{{loc.faq}}</button>
                </div>
            </div>
        </div>

        <div class="subscription-plans" :class="[hasPlan ? 'display-grid grid-column-1-2 grid-gap-space-lg' : '']">
            <div class="vip-club-items--outer display-grid" :class="[hasPlan ? 'grid-column-1-eq justify-items-center' : 'grid-column-3-eq']">
                <sub-item v-for="sub in subs" :key="sub.sku" :item="sub" :loc="loc" :upgrade-name="upgradeName" :is-subscriber="isSubscriber" :is-upgraded="isUpgraded" :account-set="accountSettled"></sub-item>
            </div>
            <div v-if="hasPlan" class="manage-subscription-wrapper">
                <div v-if="hasPlan" class="plan-details text-center vip-club-log-content--outer roundme_sm">
                    <h5>{{loc.sRenewalDate}}:</h5>
                        <p v-if="expireDate" class="plan-expiry">{{ expireDate }}</p>
                        <p class="manage-details" v-html="manageInfo"></p>
                    <button class="ss_button btn_manage_sub btn_sm btn_yolk bevel_yolk btn_vip-button bevel_blue"" v-on:click="onManageClick">{{loc.sManageBtn}}</button>
                </div>
            </div>
        </div>
    </div>
</script>

<template id="comp-sub-item">
    <div v-if="isActive" class="vip-item" :class="nameLowerCaseHyphens">
        <div class="vip-item--inner centered">
            <div class="subscription-details">
                <header>
                    <h3>{{loc[name]}}</h3>
                </header>
                <p class="price-tag roundme_sm" v-html="priceTag"></p>
                <button v-if="!hasSub" class="ss_button btn_sm btn_vip-button bevel_blue" v-on:click="subClick">
                    {{loc[buyBtnText]}}
                </button>
            </div>
        </div>
		<div v-if="flagText" class="vip-banner" :class="flagText">
            <span>{{loc[flagText]}}</span>
        </div>
        <img aria-hidden="true" :src="img" />
    </div>
</template>
<script>
    const comp_sub_item = {
        template: '#comp-sub-item',
        props: ['loc', 'item', 'upgradeName', 'isUpgraded', 'isSubscriber'],
        data() {
            return {
                isCurrentSub: false,
                hasSub: false,
                hasUpgrade: false,
                locName: '',
            };
        },
        methods: {
            subClick() {

                if (this.$parent.$el.id === 'shell-subscriptions') {
                    this.$parent.$parent.hide();
                } else {
                    this.$parent.hide();
                }

                BAWK.play('ui_click');

                if (this.hasSub) {
                    extern.buyProductForMoney();
                } else {
                    extern.buyProductForMoney(this.item.sku, true);
                    ga('send', 'event', 'subscriptions', 'click', this.locName);
                }
            }
        },
        computed: {
            nameLowerCaseHyphens() {
                this.locName =  this.item.name.replace(' ', '-').toLowerCase().replace(' ', '-');
                return this.locName;
            },
            name() {
                return `s-${this.locName}-title`;
            },
            img() {
                return `img/vip-club/vip-club-popup-item-${this.locName}-bg.png`;
            },
            priceTag() {
                let price = this.item.salePrice ? this.item.salePrice : this.item.price,
                    priceWcents = `${(price / 100).toFixed(2)}`,
                    thePrice = priceWcents.split('.');
                return `$${thePrice[0]}<span class="price-tag--cents">.${thePrice[1]}</span>`;
            },
            buyBtnText() {
                if (this.isCurrentSub && this.hasUpgrade) {
                    return 'sManageBtn';
                }
                return 's_btn_txt_subscribe';
            },
            isActive() {
                if (this.isCurrentSub && this.hasUpgrade) {
                    return true
                } else if (this.hasSub && this.hasUpgrade) {
                    return false
                } else {
                    return true
                };
            },
            flagText() {
                if (this.isCurrentSub && this.hasUpgrade) return 'p_egg_shop_purchased';
                return this.item.flagText;
            }
        },
        watch: {
            upgradeName(val) {
                
            },
            isUpgraded(val) {
                this.hasUpgrade = val;
                this.hasSub = this.isSubscriber && this.hasUpgrade;
                this.isCurrentSub = this.upgradeName === this.item.name && this.hasUpgrade;
            }
        }
    };
</script>
<script>
var compVipClubTemplate = {
    template: '#vip-club-template',
    components: {
        'sub-item': comp_sub_item,
    },
    data() {
        return vueData;
    },
    props: ['loc', 'subs'],
    
    methods: {
        onManageClick() {
            if (this.$parent.$el.id === 'shell-subscriptions') {
                this.$parent.$parent.hide();
            } else {
                this.$parent.hide();
            }
            extern.buyProductForMoney();
        },
        openVipPopup() {
            if (this.$parent.$el.id === 'shell-subscriptions') {
                this.$parent.$parent.hide();
            } else {
                this.$parent.hide();
            }
            BAWK.play('ui_click');
            vueApp.showVipPopup();
        }
    },
    computed: {
        hasPlan() {
            return this.isSubscriber && this.isUpgraded;
        },
        expireDate() {
            if (this.hasPlan) {
                return new Date(extern.account.upgradeExpiryDate).toUTCString();
            }
            return;
        },
        planName() {
            if (this.hasPlan) {
                return 's-' + this.upgradeName.replace(' ', '-').toLowerCase().replace(' ', '-') + '-title';
            }
            return;
        },
        manageInfo() {
            return this.loc.sManageInfo;
        }
    }
};
</script><script id="give-stuff-popup" type="text/x-template">
   <!-- Popup: Give Stuff -->
   <!-- <large-popup id="giveStuffPopup" ref="giveStuffPopup" :popup-model="giveStuffPopup" @popup-closed="onSharedPopupClosed" :class="giveStuffPopup.type"> -->
   <large-popup id="giveStuffPopup" ref="giveStuffPopup" :popup-model="giveStuffPopup" :class="giveStuffPopup.type">
        <template slot="content">
			<div id="giveStuffPopup-content" class="giveStuffPopup-content" :class="{'fullwidth' : giveStuffPopup.type === 'twitchDrops'}">
				<div v-if="giveStuffPopup.type === 'vip'" id="give-stuff-icon" class="give-stuff-icon">
					<img src="img/vip-club/vip-club-popup-emblem.png" alt="Shell Shockers VIP">
				</div>
				<h3 v-if="giveStuffPopup.type !== 'twitchDrops'" :class="{'popup-title-vip' : giveStuffPopup.type === 'vip'}" id="popup_title" class="roundme_sm text-center">
					{{ loc[giveStuffPopup.titleLoc] }}
				</h3>

				<h2 v-if="giveStuffPopup.type === 'twitchDrops'" id="popup_title" class="roundme_sm text-center title-shadow text-twitch-yellow">
					{{ loc[giveStuffPopup.titleLoc] }}
				</h2>

				<p v-if="giveStuffPopup.type === 'twitchDrops'" class="text-center">{{ loc.give_stuff_twitch_sub_desc }}</p>
				<div v-show="(giveStuffPopup.eggs)" class="f_row">
					<div class="egg-give-stuff">
						<img src="img/ico_goldenEgg.png" />
						<h2>{{giveStuffPopup.eggs}}</h2>
					</div>
				</div>
				<div v-show="giveStuffPopup.rickroll" class="f_row">
					<img src="img/rickroll.gif" style="margin-bottom: 1em; height: 25em;" />
				</div>
				<div v-show="giveStuffPopup.eggOrg" class="f_row">
					<img src="img/egg-org/eggOrg_timeTravel_splash800x600-min.png" style="margin-bottom: 1em;">
				</div>
				<div v-show="(giveStuffPopup.items && giveStuffPopup.items.length > 0)" class="items-container f_row gap-1 " :class="{'popup-items-vip' : giveStuffPopup.type === 'vip'}">
					<item v-for="i in giveStuffPopup.items" :loc="loc" :item="i" :key="i.id" :isSelected="false" :show-item-only="true"></item>
				</div>
				<p v-if="giveStuffPopup.type === 'twitchDrops'"></p>
			</div>
            <footer :class="{'text-center' : giveStuffPopup.type === 'twitchDrops'}">
				<!-- <p v-if="giveStuffPopup.type === 'twitchDrops'" class="text-center">{{ loc.give_stuff_twitch_footer_desc }}</p> -->
                <button class="ss_button width_xs text-center" :class="giveStuffPopup.type === 'twitchDrops' ? 'twitch-btn twitch-btn-purple' : 'btn_green bevel_green'" @click="onGiveStuffComplete">{{ loc.ok }}</button>
				<button v-if="giveStuffPopup.type === 'twitchDrops'"class="ss_button twitch-btn twitch-btn-pink width_xs text-center" @click="onClickTwitchDropsMore">{{ loc.eq_buy_stuff }}</button>
            </footer>

        </template>
        <template slot="confirm">{{ loc.confirm }}</template>
    </large-popup>
</script>


<script>
const GIVESTUFFPOPUP = {
	template: '#give-stuff-popup',
	components: {
		'item': comp_item,
	},
	props: ['loc', 'giveStuffPopup'],

	data: function () {
		return {
			languageCode: this.selectedLanguageCode,
			eggBalance: 0,
			vueData,
		}
	},
	methods: {
		onGiveStuffComplete: function () {
			this.giveStuffPopup.eggOrg = false;
			this.giveStuffPopup.rickroll = false;
			vueApp.onGiveStuffComplete();
        },
		onClickTwitchDropsMore() {
			window.open(dynamicContentPrefix + 'twitch');
			this.onGiveStuffComplete();
		}
	},
};
</script>


<script>

	function startVue(languageCode, locData) {

		vueData.extern = extern;
		vueData.loc = locData;
		
		vueApp = new Vue({
			el: '#app',
			components: {
				'dark-overlay': comp_dark_overlay,
				'light-overlay': comp_light_overlay,
				'spinner-overlay': comp_spinner_overlay,
				'gdpr': comp_gdpr,
				'settings': comp_settings,
				'help': comp_help,
				'vip-help': vip_help,
				'subscription-store': compVipClubTemplate,
				// 'subscription-store': comp_egg_store,
				'item': comp_item,
				'home-screen': comp_home_screen,
				'equip-screen': comp_equip_screen,
				'game-screen': comp_game_screen,
				// 'gold-chicken-popup': comp_gold_chicken_popup,
				'chicken-nugget-popup': comp_chickn_winner_popup,
				'egg-store-item': comp_store_item,
				'give-stuff-popup': GIVESTUFFPOPUP,
				'main-sidebar': COMPMAINSIDE,
				'account-panel': comp_account_panel,
				'house-ad': CompHouseAd,
			},

			data: vueData,

			createdTime: null,
			mountedTime: null,
			fullyRenderedTime: null,
			
			multisizeAdTag: null,

			created () {
				console.log('Vue instance created');
				createdTime = performance.now();
				this.currentLanguageCode = languageCode;
				this.urlParams = parsedUrl.query.open ? parsedUrl.query.open : null;
			},

			mounted () {
				console.log('Vue instance mounted');
				mountedTime = performance.now();
				console.log('create -> mount time (ms): ' + (mountedTime - createdTime));
				this.currentGameType = extern.gameType;

				// Cannot modify data within the mounted hook, so wait until next tick
				this.$nextTick(function () {
					fullyRenderedTime = performance.now();
					console.log('mounted -> fully rendered time (ms): ' + (fullyRenderedTime - mountedTime));
					console.log('created -> fully rendered time (ms): ' + (fullyRenderedTime - createdTime));

					this.ready = true;
					// vueApp.getNuggetTimer();
					
					//this.showSpinner('ui_game_loading', 'ui_game_waitforit');
					// this.histPushState({game: this.screens.home}, 'Shellshockers home', '?home');
					//this.playMusic();
					this.getLocLang();
					extern.continueStartup();
					this.changelog.version = extern.version;
					this.changelog.current = extern.changelogData;
					//this.fetchSponsors();
				});
			},

			methods: {
				getGameElements: function () {
					return {
						canvas: this.$refs.canvas,
						canvasWrapper: this.$refs.canvasWrapper,
					}
				},

				playMusic: function () {
					myAudio = new Audio('./sound/theme');
					// Uncomment for looping.
					// myAudio.addEventListener('ended', function() {
					//     this.currentTime = 0;
					//     this.play();
					// }, false);
					myAudio.volume = this.volume;
					myAudio.play();
				},

				changeLanguage: function (languageCode) {
					extern.getLanguageData(languageCode, this.setLocData);
				},

				setLocData: function (languageCode, newLocData) {
					this.currentLanguageCode = getStoredString('languageSelected', null) ? localStore.getItem('languageSelected') : languageCode;
					this.loc = newLocData;
				},

				setPlayerName: function (playerName) {
					this.playerName = playerName.substring(0, 128);
				},

				showSpinner: function (headerLocKey, footerLocKey) {
					this.$refs.spinnerOverlay.show(headerLocKey, footerLocKey);
				},

				showSpinnerLoadProgress: function (percent) {
					this.$refs.spinnerOverlay.showSpinnerLoadProgress(percent);
				},

				hideSpinner: function () {
					this.$refs.spinnerOverlay.hide();
				},

				onSettingsPopupOpened: function () {
					this.$refs.settings.captureOriginalSettings();
					this.sharedIngamePopupOpened();
				},

				onSettingsPopupSwitchTabMisc: function () {
					this.$refs.settings.switchTab('misc_button');
				},

				onSettingsX: function () {
					this.$refs.settings.applyOriginalSettings();
					this.$refs.settings.cancelLanguageSelect();
					this.sharedIngamePopupClosed();
				},

				onSettingsQuickSave() {
					this.$refs.settings.quickSave();
				},

				onNoAnonPopupConfirm: function () {
					ga('send', 'event', this.googleAnalytics.cat.playerStats, this.googleAnalytics.action.denyAnonUserPopup, this.googleAnalytics.label.signInClick);
					this.showFirebaseSignIn();
				},

				onSharedPopupClosed: function () {
					// If in-game, show game menu after closing the popup
					this.blackFridayBanner = false;
					if (this.showScreen === this.screens.game && extern.inGame) {
						this.showGameMenu();
					}
				},

				sharedIngamePopupOpened() {
					if (extern.inGame) this.$refs.gameScreen.sharedPopupOpened();
				},

				sharedIngamePopupClosed() {
					if (extern.inGame) this.$refs.gameScreen.sharedPopupClosed();
				},

				onGiveStuffComplete: function () {
					this.$refs.giveStuffPopup.$refs.giveStuffPopup.hide();
					if (extern.inGame) {
						this.showGameMenu();
						if (this.$refs.equipScreen.showScreen === this.$refs.equipScreen.screens.equip) {
							vueApp.setDarkOverlay(false);
						}
					}
				},

				onPrivacyOptionsOpened: function () {
					this.showPrivacyPopup();
				},
				/**
				 * Creates a generic popup that passes content 3 data options to slots on the genericPopup smallPopup
				 * @param titleLockKey mixed - popup header text
				 * @param contentLocKey mixed- popup content
				 * @param confirmLocKey mixed - popup button text
				 */
				showGenericPopup: function (titleLocKey, contentLocKey, confirmLocKey, hideBackgroundPopup) {
					this.genericMessagePopup.titleLocKey = titleLocKey;
					this.genericMessagePopup.contentLocKey = contentLocKey;
					this.genericMessagePopup.confirmLocKey = confirmLocKey;
					this.hidePausePopupIfGenericPopupOpen();
					this.$refs.genericPopup.show();

					// vueApp.setDarkOverlay();
				},
				hidePausePopupIfGenericPopupOpen: function() {

					if (!this.$refs.gameScreen.$refs.pausePopup && $refs.gameScreen.$refs.pausePopup.isShowing === false) {
						return;
					}

					// return this.$refs.gameScreen.$refs.pausePopup.hide();
				},
				showOpenUrlPopup: function (url, titleLocKey, content, confirmLocKey, cancelLocKey) {
					console.log('title: ' + this.loc[titleLocKey]);
					console.log('confirm: ' + this.loc[confirmLocKey]);
					console.log('cancel: ' + this.loc[cancelLocKey]);

					this.openUrlPopup.url = url;
					this.openUrlPopup.titleLocKey = titleLocKey;
					this.openUrlPopup.content = content;
					this.openUrlPopup.confirmLocKey = confirmLocKey;
					this.openUrlPopup.cancelLocKey = cancelLocKey;
					this.$refs.openUrlPopup.show();
				},

				onOpenUrlPopupConfirm: function () {
					extern.openUrlAndGiveReward();
				},

				showUnsupportedPlatformPopup: function (contentLocKey) {
					this.showScreen = -1;
					this.unsupportedPlatformPopup.contentLocKey = contentLocKey;
					this.$refs.unsupportedPlatformPopup.show();
				},

				showMissingFeaturesPopup: function () {
					this.showScreen = -1;
					this.$refs.missingFeaturesPopup.show();
				},

				showFirebaseSignIn: function () {
					this.$refs.homeScreen.showSignIn();
				},

				hideFirebaseSignIn: function () {
					this.$refs.firebaseSignInPopup.hide();
				},

				showCheckEmail: function () {
					this.$refs.homeScreen.$refs.checkEmailPopup.show();
				},

				hideCheckEmail: function () {
					this.$refs.homeScreen.$refs.checkEmailPopup.hide();
				},

				showResendEmail: function () {
					this.$refs.homeScreen.$refs.resendEmailPopup.show();
				},

				hideResendEmail: function () {
					this.$refs.homeScreen.$refs.resendEmailPopup.hide();
				},

				showChickenPopup: function () {
					this.$refs.goldChickenPopup.show();
				},

				hideChickenPopup: function () {
					this.$refs.goldChickenPopup.hide();
				},

				showHelpPopup: function () {
					if (!extern.inGame) {
						this.hideGameMenu();
					}
					this.$refs.helpPopup.show();
				},

				showHelpPopupFeedbackWithDelete() {
					this.$refs.help.openFeedbackTabWith(this.feedbackType.delete.id);
					this.showHelpPopup();
				},

				showVipPopup: function () {
					if (!extern.inGame) {
						this.hideGameMenu();
					}
					BAWK.play('ui_popupopen');
					this.$refs.vipPopup.show();
				},

				showGetMobilePopup() {
					this.$refs.mobileAdPopup.show();
				},

				showAttentionPopup: function () {
					if (!extern.inGame) {
						this.hideGameMenu();
					}
					this.$refs.anonWarningPopup.show();
				},

				hideHelpPopup: function () {
					this.$refs.helpPopup.hide();
				},

				showSettingsPopup: function () {
					if (!extern.inGame) {
						this.hideGameMenu();
					}
					this.$refs.settingsPopup.show();
					extern.settingsMenuOpened();
					this.sharedIngamePopupOpened();
				},

				hideSettingsPopup: function () {
					this.$refs.settingsPopup.hide();
				},

            showChicknWinnerPopup: function () {
				if (!this.chwPopupOpen) {
					this.$refs.chicknWinner.show();
					vueApp.$refs.chickenNugget.showAd();
				}
				this.chwPopupOpen = true;
				this.isBuyNugget = true;
            },

            showEggStorePopup: function () {
                this.$nextTick(() => {
                    this.hideGameMenu();
                    this.$refs.eggStorePopup.show();
                    if (this.isSale) {
                        this.blackFridayBanner = true;
                    }
                    ga('send', 'event', this.googleAnalytics.cat.itemShop, this.googleAnalytics.action.shopItemNeedMoreEggsPopup, this.googleAnalytics.label.getMoreEggs);
                });
            },

				showSubStorePopup: function () {
					this.$nextTick(() => {
						if (!extern.inGame) {
							this.hideGameMenu();
						}
						this.$refs.subStorePopup.show();
						BAWK.play('ui_popupopen');
						// ga('send', 'event', this.googleAnalytics.cat.itemShop, this.googleAnalytics.action.shopItemNeedMoreEggsPopup, this.googleAnalytics.label.getMoreEggs);
					});
				},

				vipEndedPopup() {
					this.$refs.vipEnded.show();
					BAWK.play('ui_popupopen');
				},

				showPopupEggStoreSingle(sku) {
					if (!sku) {
						console.log('No sku for popup');
						return;
					}
					if (!this.premiumShopItems.some( i => i.sku === sku)) {
						vueApp.showGenericPopup("uh_oh", "p_egg_shop_no_item_desc", "ok");
						return;
					}

					this.eggStorePopupSku = sku;
					this.$refs.popupEggStoreSingle.show();
				},

				hidePopupEggStoreSingle() {
					this.eggStorePopupSku = null;
					this.$refs.popupEggStoreSingle.hide();
				},

				hideEggStorePopup: function () {
					this.$refs.eggStorePopup.hide();
				},

				showChangelogPopup: function () {
					this.$refs.changelogPopup.show();
				},

				showHistoryChangelogPopup() {
					fetch('./changelog/oldChangelog.json', {cache: "no-cache"})
						.then(response => response.json())
						.then(data => {
							data.forEach(el => this.changelog.current.push(el));
					});

					this.changelog.showHistoryBtn = false;
				},

				hideChangelogPopup: function () {
					this.$refs.changelogPopup.hide();
				},

				showGiveStuffPopup: function (titleLoc, eggs, items, type, callback) {
					if (this.giveStuffPopup.eggOrg) {
						ga('send', 'event', 'Egg Org', 'Code Cracked', 'redeemed');
					}
					type = type || '';
					this.giveStuffPopup.titleLoc = titleLoc;
					this.giveStuffPopup.eggs = eggs;
					this.giveStuffPopup.items = items;
					this.giveStuffPopup.type = type;
					this.$refs.giveStuffPopup.$refs.giveStuffPopup.show();
					if (callback) callback();
				},

				showEggOrgPopup() {
					this.giveStuffPopup.eggOrg = true;
					this.showGiveStuffPopup('p_give_stuff_title');
				},

				showShareLinkPopup: function (url) {
					if (!extern.inGame) {
						this.hideGameMenu();
					}
					this.game.shareLinkPopup.url = url;
					this.$refs.gameScreen.$refs.shareLinkPopup.show();
				},

				hideShareLinkPopup() {
					if (!this.$refs.gameScreen.$refs.shareLinkPopup.isShowing) {
						return;
					}
					this.$refs.gameScreen.$refs.shareLinkPopup.hide();
				},

				showJoinPrivateGamePopup: function (code) {
					this.$refs.homeScreen.$refs.playPanel.showJoinPrivateGamePopup(code);
				},

				showPrivateGamePopup() {
					this.$refs.homeScreen.$refs.playPanel.$refs.createPrivateGamePopup.toggle();
				},

				onBackClick() {
					this.$refs.equipScreen.onBackClick();
				},

				switchToHomeUi: function () {
					this.showScreen = this.screens.home;
					BAWK.play('ui_toggletab');
					vueApp.showTitleScreenAd();
					this.gameUiRemoveClassForNoScroll();
					extern.chwRadialProgress();
				},

				switchToProfileUi: function () {
					this.showScreen = this.screens.profile;
					BAWK.play('ui_toggletab');
					vueApp.showTitleScreenAd();
					// this.histPushState({game: this.screens.home}, 'Shellshockers home', '?home');
					this.gameUiRemoveClassForNoScroll();
				},

				switchToEquipUi: function (mode) {
					this.showScreen = this.screens.equip;

					this.$refs.equipScreen.setup();
					this.$refs.equipScreen.switchTo(mode);

					if (extern.inGame) {
						this.hideGameMenu();
						extern.openEquipInGame();
					}
					else {
						vueApp.hideTitleScreenAd();
					}
				},

				switchToGameUi: function (isGameOwner) {
					this.showScreen = this.screens.game;
					this.game.isGameOwner = isGameOwner;
				},

				gameUiAddClassForNoScroll() {
					let html = document.getElementsByTagName("html")[0];
					html.classList.add('noScrollIngame');
				},

				gameUiRemoveClassForNoScroll() {
					let html = document.getElementsByTagName("html")[0];
					html.classList.remove('noScrollIngame');
				},

				switchToGameUiQuickPlay(isGameOwner) {
					this.showScreen = this.screens.game;
					this.game.isGameOwner = isGameOwner;
					this.ui.showCornerButtons = false;
					vueApp.hideTitleScreenAd();
					this.gameUiAddClassForNoScroll();
				},

				showGameMenu: function () {
					// this.hideSpinner();
					this.$refs.gameScreen.showGameMenu();
					// this.histPushState({game: this.screens.game}, 'Shellshockers game menu', '?game=menu');
				},

				hideGameMenu: function () {
					this.$refs.gameScreen.hideGameMenu();
					// this.histPushState({game: this.screens.game}, 'Shellshockers in game', '?game=play');
				},

				onMiniGameCompleted: function () {
					this.$refs.homeScreen.onMiniGameCompleted();
				},

				setShellColor: function (colorIdx) {
					this.equip.colorIdx = colorIdx;
				},

				setAccountUpgraded: function (upgraded, endDate) {
					this.isUpgraded = upgraded;
					this.equip.extraColorsLocked = !this.isUpgraded;
					this.nugStart = endDate;

				},

				setDarkOverlay: function (visible, overlayClass) {
					this.$refs.darkOverlay.show = visible;
					this.$refs.darkOverlay.overlayClass = overlayClass;
				},

				setLightOverlay: function (visible, overlayClass) {
					this.$refs.lightOverlay.show = visible;
					this.$refs.darkOverlay.overlayClass = overlayClass;
				},

				authCompleted: function () {
					this.accountSettled = true;
					if (vueApp.$refs.firebaseSignInPopup.isShowing) this.hideFirebaseSignIn();

				},

				showItemOnEquipScreen: function (item) {
					this.switchToEquipUi();
					this.$refs.equipScreen.autoSelectItem(item);
				},

				showTaggedItemsOnEquipScreen: function (tag) {
					this.switchToEquipUi();
					this.$refs.equipScreen.showTaggedItems(tag);
				},

				showSelectedTaggedItemsOnEquipScreen: function (tag) {
					this.switchToEquipUi();
					this.$refs.equipScreen.showSelectedTagItems(tag);
				},

				useHouseAdSmall: function (smallHouseAd) {
					this.ui.houseAds.small = smallHouseAd;
				},

				useHouseAdBig: function (bigHouseAd) {
					this.ui.houseAds.big = bigHouseAd;
				},

				denyAnonUser: function () {
					ga('send', 'event', vueApp.googleAnalytics.cat.playerStats, vueApp.googleAnalytics.action.denyAnonUserPopup);
					if (extern.inGame) {
						this.hideGameMenu();
					}
					this.$refs.noAnonPopup.show();
				},

				showGdprNotification: function () {
					this.$refs.gdpr.show();
				},

				showPrivacyPopup: function () {
					this.hideSettingsPopup();
					this.$refs.privacyPopup.show();
				},

				hidePrivacyPopup: function () {
					this.$refs.privacyPopup.hide();
					this.showSettingsPopup();
				},

				ofAgeChanged: function () {
					extern.setOfAge(this.isOfAge);
					BAWK.play('ui_onchange');
				},

				targetedAdsChanged: function () {
					extern.setTargetedAds(this.showTargetedAds);
					BAWK.play('ui_onchange');
				},

				setPrivacySettings: function (ofAge, targetedAds) {
					this.isOfAge = ofAge;
					this.showTargetedAds = targetedAds;
				},

				gameJoined: function (gameType, team) {
					this.game.gameType = gameType;
					this.setTeam(team);
				},

				setTeam: function (team) {
					if (hasValue(team)) {
						this.game.team = team;
					}
				},

				showGoldChickenPopup: function () {
					this.$refs.goldChickenPopup.show();
				},

				hideGoldChickenPopup: function () {
					this.$refs.goldChickenPopup.hide();
				},

				showChicknWinnerPopup: function () {
					this.$refs.chicknWinner.show();
					vueApp.$refs.chickenNugget.showAd();
					// this.$refs.chickenNugget.loadMiniGame();
					this.isBuyNugget = true;
					console.log('showChicknWinnerPopup()');
				},

				hideChicknWinnerPopup: function () {
					this.$refs.chicknWinner.hide();
					this.$refs.chickenNugget.onGotWinner();
					console.log('Hide nugget');
				},

				chicknWinnerIsReady() {
					if (this.chicknWinnerDailyLimitReached) {
						this.chicknWinnerNotReady();
						return;
					}
					setTimeout(() => {
						this.chicknWinnerReady = true;
						this.hasChwPlayClicked = false;
						ga('send', 'event', 'Chickn Winner', 'Free eggs btn', `ready-in-${(this.showScreen === this.screens.game) ? 'game' : 'home'}`);
					}, 1000);
				},

				chicknWinnerNotReady() {
					this.chicknWinnerReady = false;
					this.hasChwPlayClicked = false;
				},

				chicknWinnerError() {
					this.isChicknWinnerError = true;
				},

				chicknWinnerDailyLimit() {
					this.chicknWinnerDailyLimitReached = true;
					this.chicknWinnerNotReady();
				},

				loadNuggetVideo() {
					if (!extern.inGame) {
						this.hideGameMenu();
					}
					extern.checkStartChicknWinner(true);
					BAWK.play('ui_playconfirm');
				},

			chwResetAfterWin() {
				this.chwPopupOpen = false;
				this.miniEggGameAmount = 0;
			},

			chwDoIncentivized() {
				extern.checkStartChicknWinner(true);
			},

            loadNuggetVideo() {
                this.hideGameMenu();
                this.chwDoIncentivized();
                BAWK.play('ui_playconfirm');
            },

				placeBannerAdTagForNugget: function (tagEl) {
					this.$refs.chickenNugget.placeBannerAdTag(tagEl);
				},

				useSpecialItemsTag: function (tag) {
					this.equip.specialItemsTag = tag;
					this.equip.showSpecialItems = true;
				},

				disableSpecialItems: function () {
					this.equip.showSpecialItems = false;
				},

				setUiSettings: function (settings) {
					this.settingsUi.settings = settings;
					this.$refs.settings.setSettings(settings);
				},

				leaveGame: function () {
					this.$refs.gameScreen.leaveGame();
				},

				showPlayerActionsPopup: function (slot) {
					if (this.showAdBlockerVideoAd) {
						return;
					}

					this.playerActionsPopup = slot;
					this.$refs.gameScreen.showPlayerActionsPopup();
				},
				onSignInCancelClicked: function () {
					vueApp.$refs.firebaseSignInPopup.hide();
					BAWK.play('ui_popupclose');
				},
				anonWarningPopupCancel: function() {
					let anonWarnConfrimed = localStore.getItem('anonWarningConfirmed');
					this.urlParamSet = this.urlParams ? true : null;
					this.shellShockUrlParamaterEvents();
					ga('send', 'event', this.googleAnalytics.cat.playerStats, this.googleAnalytics.action.anonymousPopupOpenAuto, this.googleAnalytics.label.understood);
					return anonWarnConfrimed === null && localStore.setItem('anonWarningConfirmed', true);
				},
				anonWarningPopupConfrim() {
					let anonWarnConfrimed = localStore.getItem('anonWarningConfirmed');
					anonWarnConfrimed === null && localStore.setItem('anonWarningConfirmed', true);
					ga('send', 'event', this.googleAnalytics.cat.playerStats, this.googleAnalytics.action.anonymousPopupOpenAuto, this.googleAnalytics.label.signInClick);
					extern.showSignInDialog();
					this.urlParamSet = false;
					vueApp.$refs.firebaseSignInPopup.show();
				},
				conditionalAnonWarningCall: function() {
					let anonWarnConfrimed = localStore.getItem('anonWarningConfirmed');
					
					if ( ! hasValue(anonWarnConfrimed) && this.isAnonymous) {
						vueApp.showAttentionPopup();
						ga('send', 'event', this.googleAnalytics.cat.playerStats, this.googleAnalytics.action.anonymousPopupOpenAuto);
					}

				},
				needMoreEggsPopupCall: function() {
					ga('send', 'event', this.googleAnalytics.cat.itemShop, this.googleAnalytics.action.shopItemNeedMoreEggsPopup);
					this.$refs.needMoreEggsPopup.show();
				},
				/**
				 * Not 100 % certain this should live in vue but here it is.
				 * Add the ability to use url paramaters to trigger events in the game.
				 * e.g. shellshock.io/?open=eggStore&type=Hat&item=1111 will open the spiderman hat item.
				 * Called in the extern closure under gameApp.js => afterGameReady()
				 */
				shellShockUrlParamaterEvents() {
					// VUE next tick https://vuejs.org/v2/api/#Vue-nextTick
					this.$nextTick( ()=> {
						this.doSsUlrParams();
					});
				},

				doSsUlrParams() {
					if ( ! this.urlParams) {
						return;
					}

					console.log(hasValue(this.isAnonymous));

					if (hasValue(this.ui.houseAds.big)) {
						this.urlParamSet = false;
						return;
					} else if (this.isAnonymous && ! hasValue(localStore.getItem('anonWarningConfirmed'))) {
						this.urlParamSet = false;
						console.log('Almost there!');
						this.conditionalAnonWarningCall();
						return;
					}

					console.log('Passed Popup gate');

					switch (this.urlParams) {
						case 'eggStore' :
							// Opens the purchase egg store popup
							this.showEggStorePopup();
							break;
						case 'goldenChicken' :
							// Opens the golden chicken popup
							vueApp.$refs.goldChickenPopup.show();
							break;
						case 'twoTimesTheEggs' :
							// Opens the chicken nugget video
								extern.checkStartChicknWinner(true);
								BAWK.play('ui_playconfirm');
							break;
						case 'itemShop' :
							// Opens shop options
							// /?open=itemShop
							// /?open=itemShop&type=Hat/Stamp/Primary/Secondary/Grenade/Premium/Tagged 
							// /?open=itemShop&gunClass=Soldier/Soldier/Scrambler/Ranger/Eggsploder/Whipper/Crackshot/TriHard
							// /?open=itemShop&item=1111 opens hat store and then selects item
							// /?open=itemShop&item=1111&openBuyNow=1 opens hat store, selects item and then opens items popup
							this.eggStoreUrlParams();
							break;
						case 'vipStore' :
							vueApp.showSubStorePopup();
							break;
						case 'redeem' :
							vueApp.switchToEquipUi(this.equipMode.inventory);
							BAWK.play('ui_popupopen');
							this.$refs.equipScreen.$refs.redeemCodePopup.show();
							if ('code' in parsedUrl.query) this.equip.redeemCodePopup.code = parsedUrl.query.code;
							break;
						case 'faq' :
							this.showHelpPopup();
							break;
						case 'taggedItems' : 
							this.openSpecialTagItemsTab();
							break;
						case 'privateGame' :
							this.showPrivateGamePopup();
							break;
						case 'kotcInstruction' : 
							this.showKotcInstrucPopup();
							break;
						default:
							null;
					};
				},

				eggStoreUrlParams() {
					setTimeout(() => {
						const   ITEMIDPARAM = parsedUrl.query.hasOwnProperty('item') ? parsedUrl.query.item : '',
								TYPEPARAM = parsedUrl.query.hasOwnProperty('type') ? parsedUrl.query.type : '',
								GUNCLASS = parsedUrl.query.hasOwnProperty('gunClass') && CharClass.hasOwnProperty(parsedUrl.query.gunClass) ? parsedUrl.query.gunClass : '',
								BUYNOWPOPUP = parsedUrl.query.hasOwnProperty('openBuyNow');

						if (ITEMIDPARAM) {
							const IDISNUMBER = parseInt(ITEMIDPARAM);
								let item = extern.catalog.findItemById(IDISNUMBER);
								if (!item.is_available) {
									vueApp.showGenericPopup("uh_oh", "no_anon_title", "ok")
									return;
								}
								this.switchToEquipUi();
								this.$refs.equipScreen.autoSelectItem(item);

								if (BUYNOWPOPUP === true) {
									console.log('Will it open?');
									vueApp.$refs.equipScreen.onBuyItemClicked();
									return;
								}
						} else {
							this.openEquipUISwitchToShop();
							if (GUNCLASS) {
								vueApp.$refs.equipScreen.switchItemType(ItemType['Primary']);
								vueApp.$refs.equipScreen.$refs.weapon_select.selectClass(CharClass[GUNCLASS]);
								console.log('Has GUN');
							} else if (TYPEPARAM) {
								console.log('OPEN item');
								if (ItemType.hasOwnProperty(TYPEPARAM)) {
									vueApp.$refs.equipScreen.switchItemType(ItemType[TYPEPARAM]);
								} else {
									if (TYPEPARAM === 'Premium') {
										vueApp.$refs.equipScreen.onPremiumItemsClicked();
									} else if (TYPEPARAM === 'Tagged') {
										vueApp.$refs.equipScreen.onTaggedItemsClicked();
									}
								}
							}
						}

					}, 45);
				},
				openSpecialTagItemsTab() {
					let tag = parsedUrl.query.tag ? parsedUrl.query.tag : null;
					vueApp.showSelectedTaggedItemsOnEquipScreen(tag);
				},

				openEquipUISwitchToShop() {
					vueApp.switchToEquipUi(this.equipMode.shop);
					vueApp.$refs.equipScreen.switchTo(this.equipMode.shop);
				},

				openEquipUISwitchToInventory() {
					vueApp.switchToEquipUi(this.equipMode.shop);
					vueApp.$refs.equipScreen.switchTo(this.equipMode.inventory);
				},

				delayInGamePlayButtons() {
					vueApp.$refs.gameScreen.delayGameMenuPlayButtons();
				},
				//Call/hide display ads
				hideRespawnDisplayAd() {
					if (this.isSubscriber) {
						return;
					}
					this.$refs.gameScreen.$refs.respawnDisplayAd.hide();
					this.$refs.gameScreen.$refs.respawnTwoDisplayAd.hide();
				},
				showRespawnDisplayAd() {
					if (this.isSubscriber) {
						return;
					}
					this.$refs.gameScreen.$refs.respawnDisplayAd.show();
					this.$refs.gameScreen.$refs.respawnTwoDisplayAd.show();
				},
				hideLoadingScreenAd() {
					this.$refs.spinnerOverlay.$refs.loadingScreenDisplayAd.hide()
				},
				showLoadingScreenAd() {
					this.$refs.spinnerOverlay.$refs.loadingScreenDisplayAd.show();
					// this.histPushState({game: 3}, 'Shellshockers Loading display ad', '?loadingAd=true');
				},
				displayAdEventObject(event) {
					let object = event;
					this.displayAdObject = object.size[0];
				},
				showTitleScreenAd() {
					if (this.isSubscriber) {
						return;
					}
					this.$refs.homeScreen.$refs.titleScreenDisplayAd.show();
				},
				hideTitleScreenAd() {
					if (this.isSubscriber) {
						return;
					}
					this.$refs.homeScreen.$refs.titleScreenDisplayAd.hide();
				},
				toggleTitleScreenAd() {
					if (this.isSubscriber) {
						return;
					}
					this.$refs.homeScreen.$refs.titleScreenDisplayAd.toggleAd();
				},
				scrollToTop() {
					let position =
						document.body.scrollTop || document.documentElement.scrollTop,
						scrollAnimation;
					if (position) {
						window.scrollBy(0, -Math.max(1, Math.floor(position / 10)));
						scrollAnimation = setTimeout(this.scrollToTop, 10);
					} else clearTimeout(scrollAnimation);
				},
				externPlayObject(playType, gameType, playerName, mapIdx, joinCode) {
					extern.play({playType, gameType, playerName, mapIdx, joinCode});
				},
				pleaseWaitPopup() {
					vueApp.showGenericPopup("signin_auth_title", "signin_auth_msg");
				},
				isPlayingPoki() {
					this.isPoki = true;
					this.ready = true;
					return;
				},
				histPushState(obj, title, param) {
					return history.pushState(obj, title, param);
				},
				disablePlayButton(val) {
					const playBtns = document.querySelectorAll('.is-for-play');
					playBtns.forEach(btn => btn.disabled = val);
					this.playClicked = val;
					// document.querySelector('.play-button').disabled = val;
				},
				disableRespawnButton(val) {
					return document.querySelector('.btn-respawn') ? document.querySelector('.btn-respawn').disabled = val : '';
				},
				disaplyAdEventObject(event) {
					this.displayAdObject = event.size === null ? null : event.size[0];
				},
				adBlockerPopupToggle() {
				return vueApp.$refs.adBlockerPopup.toggle();
				},
				// musicPlayOnce() {
				//     return setTimeout(() => this.$refs.gameScreen.$refs.gameScreenMusic.playOnce(), 2000);
				// },
				// musicPause() {
				//     this.$refs.gameScreen.$refs.gameScreenMusic.pause();
				// },
				musicVolumeControl(value) {
					return;
					this.settingsUi.adjusters.music[0].value = Number(value);
					this.$refs.gameScreen.$refs.gameScreenMusic.loadVolume();
				},
				toggleMusic() {
					this.$refs.gameScreen.$refs.gameScreenMusic.toggleMusic();
				},
				musicWidget(val) {
					this.music.isMusic = val;
				},
				fetchSponsors() {
					fetch(this.music.musicJson)
						.then((response) => response.json())
						.then((sponsors) => this.music.sponsors = sponsors)
						.catch((error) => console.log('Sponsors fetch error', error));
				},
				pwaPopup() {
					return this.$refs.pwaPopup.show();
				},
				pwaBtnClick() {
					// Track the click
					ga('send', 'event', 'pwa', 'button', 'click');
					//close popup
					this.$refs.pwaPopup.hide();
					// Get the event
					this.pwaDeferEvent = extern.getPwaEvent;

					if (!this.pwaDeferEvent) {
						return;
					}
					this.pwaDeferEvent.prompt();

					this.pwaDeferEvent.userChoice
						.then((choiceResult) => {
							if (choiceResult.outcome === 'accepted') {
								console.log('User accepted the A2HS prompt');
							} else {
								console.log('User dismissed the A2HS prompt');
							}
							ga('send', 'event', 'pwa', 'a2hs', choiceResult.outcome);
							this.pwaDeferEvent = null;
						});

					this.pwaDeferEvent = '';
				},
				signOut() {
					this.isUpgraded = false;
					this.equip.extraColorsLocked = true;
					this.isUpgraded = false;
					this.upgradeName = '';
					this.isSubscriber = false;
				},
				mediaTabsStartRotate() {
					return this.$refs.homeScreen.$refs.mediaTabs.autoRotateTabs();
				},

				mediaTabsCancelRotate() {
					return this.$refs.homeScreen.$refs.mediaTabs.cancelRotate(true);
				},
				stopClicksFAdBlocker(e) {
					e.stopPropagation();
					e.preventDefault();
				},
				showAdBlockerVideo() {
					document.addEventListener('click',this.stopClicksFAdBlocker, true);
					this.bannerHouseAd = extern.getHouseAd('bigBanner');
					this.showAdBlockerVideoAd = true;
					if (!extern.inGame) {
						this.hideGameMenu();
					}
					this.$refs.adBlockerVideo.show();
				},
				hideAdBlockerVideo() {
					document.removeEventListener("click", this.stopClicksFAdBlocker, true);  
					this.$refs.adBlockerVideo.hide();
					if (extern.inGame) {
						this.showGameMenu();
					}
					this.bannerHouseAd = {};
					this.showAdBlockerVideoAd = false;


				},
				showKotcInstrucPopup() {
					this.$refs.kotcInstrucPopup.show();
				},
				kotcInstrucPopupHide() {
					this.$refs.kotcInstrucPopup.hide();
				},
				onClickPlayKotcNow() {
					this.externPlayObject(vueData.playTypes.joinPublic, 3, this.playerName, '', '');
					this.kotcInstrucPopupHide();
				},
				onVipHelpClosed() {
					this.onSharedPopupClosed();
					this.showSubStorePopup();
				},
				getLocLang(val) {
					let data = this.loc,
						langSetup = {};
					if (val) data = val;

					const newLoc = Object.entries(data).filter(item => item[0].includes('locLang')).forEach(lang => langSetup[lang[0].split('_').pop('').split("-").pop('')] = lang[1]);
					this.$nextTick(() => {
						this.locLanguage = langSetup
					});
				},
				onClickTwitchDropsMore() {
					window.open(dynamicContentPrefix + 'twitch');
					this.onGiveStuffComplete();
				},
				onPremiumItemsClicked() {
					this.openEquipUISwitchToShop();
					this.$refs.equipScreen.onPremiumItemsClicked();
				},
				showScavengerHuntPopup() {
					BAWK.play('ui_popupopen');
					this.$refs.scavengerHunt.show();
				},
				hideScavengerHuntPopup() {
					BAWK.play('ui_popupclose');
					this.$refs.scavengerHunt.hide();
				},
				useTags(val) {
					this.ui.socialMedia.selected = val.socialMedia;
					this.ui.premiumFeaturedTag = val.premFeat;
				},
				onClickScavengerPopup() {
					ga('send', 'event', 'popup', 'click', 'scavengerHuntDiscord');
					return window.open('https://discord.com/invite/bluewizard');
				},
				onAccountDelectionConfirmed() {
					this.$refs.help.onAccountDelectionConfirmed();
				},
				showDeleteAccoutApprovalPopup() {
					this.$refs.deleteAccountApprovalPopup.show();
				},
				resetFeedbackType(){
					this.feedbackSelected = 0;
				},
				playIncentivizedAd(e) {
					if (this.showAdBlockerVideoAd) {
						return;
					}
					if (!this.chicknWinnerReady || this.hasChwPlayClicked) {
						e.preventDefault();
						return;
					}
					ga('send', 'event', 'Chickn Winner', 'Free eggs btn', 'click-home');

					this.hasChwPlayClicked = true;
					vueApp.loadNuggetVideo();
					vueApp.chicknWinnerNotReady();
				},
				chwStopCycle() {
					if (this.chwHomeTimer) {
						clearInterval(this.chwHomeTimer);
						this.chwHomeTimer = '';
						this.chwHomeEl.classList.remove('.active');
					}
				},
				chwShowCycle() {
					this.chwHomeEl = document.querySelector('.chw-home-timer');
					if (this.chwHomeEl) {
					this.chwHomeTimer = setInterval(() => {
						this.chwHomeEl.classList.toggle('active');
						}, this.chwActiveTimer);
					}
				},

				// onHomeClicked() {
				// 	this.$refs.gameScreen.onHomeClicked();
				// },
				setInGame(val) {
					this.game.on = val;
				},
				setPause(val) {
					this.game.isPaused = val;
				},
				onHomeClicked: function () {
					BAWK.play('ui_click');
					this.$refs.leaveGameConfirmPopup.show();
				},
				onLeaveGameConfirm: function () {
					this.$refs.gameScreen.onLeaveGameConfirm();
				},
				onLeaveGameCancel: function () {
					this.$refs.gameScreen.onLeaveGameCancel();
				},

				leaveGame: function () {
					this.$refs.gameScreen.leaveGame();
				},
				statsLoading() {
					if (!extern.inGame) {
						this.ui.game.stats.loading = false;
						return;
					}
					this.ui.game.stats.loading = this.ui.game.stats.loading ? false : true;
				},
				onSignInClicked() {
					BAWK.play('ui_playconfirm');
					this.$refs.homeScreen.onSignInClicked();
				},
				onSignOutClicked() {
					BAWK.play('ui_reset');
					this.$refs.homeScreen.onSignOutClicked();
				},
				showNuggyPopup() {
					if (this.chicknWinnerReady && this.firebaseId !== null) {
						return this.playIncentivizedAd()
					}
					vueApp.showChicknWinnerPopup();
				},
				openUnblocked() {
					if (crazyGamesActive) {
						window.open('https://scrambled.world/unblocked');
					}
					else {
						window.open("unblocked");
					}
				},
				onGameTypeChanged(val) {
					this.currentGameType = val;
				},
				onGamePauseUi() {
					this.$refs.gameScreen.pauseUi();
				}
			},
			computed: {
				appClassObj() {
					return {
						'playing-poki': this.isPoki,
						'playing-crazy-games': crazyGamesActive,
						'is-vip': this.isSubscriber && this.isUpgraded ? true : false,
						'is-paused': this.game.isPaused && this.game.on && this.showScreen === this.screens.game
					}
				},

				appClassScreen() {
					return getKeyByValue(this.screens, this.showScreen) + '-screen';
				},

				bigBannerAdLink() {
					return this.bigHouseAd.link
				},
				bigBannerAdImg() {
					return dynamicContentPrefix + `data/img/art/${this.bigHouseAd.id}${this.bigHouseAd.imageExt}`;
				},
				showEquipScreens() {
					return this.showScreen === this.screens.equip || this.showScreen === this.screens.featured || this.showScreen === this.screens.gear || this.showScreen === this.screens.limited;
				},
				
				accountStatus() {
					if (this.isAnonymous) {
						return this.isAnonymous && hasValue(this.firebaseId) ? 'anon' : 'no-account';
					} else {
						return hasValue(this.firebaseId) ? 'signed-in' : 'no-account';
					}
				},
				chwClass() {
					if (this.chicknWinnerDailyLimitReached || this.isChicknWinnerError) {
						return 'grid-column-1-eq';
					} else {
						if (this.chicknWinnerReady) {
							return 'grid-column-1-eq';
						} else {
							return 'grid-column-1-2';
						}
					}
				},
				chwHomeTimerCls() {
					//{'chw-home-screen-max-watched': chicknWinnerDailyLimitReached}
					if (this.chicknWinnerDailyLimitReached) {
						return 'chw-home-screen-max-watched';
					} else {
						if (this.chicknWinnerReady) {
							return 'is-ready active';
						} else {
							return 'not-ready';
						}
					}
				},
				chwChickSrc() {
					if (this.chicknWinnerDailyLimitReached || this.isChicknWinnerError) {
						return 'img/chicken-nugget/chickLoop_daily_limit.svg';
					} else {
						if (!this.chicknWinnerReady) {
							return 'img/chicken-nugget/chickLoop_sleep.svg';
						} else {
							return 'img/chicken-nugget/chickLoop_speak.svg';
						}
					}
				},
				chwShowTimer() {
					if (this.chicknWinnerDailyLimitReached) {
						// this.chwStopCycle();
						return false;
					} else {
						if (this.chicknWinnerReady) {
							this.chwShowCycle();
							return false;
						} else {
							// this.chwStopCycle();
							return true;
						}
					}
				},
				remainingMsg() {
					if (this.isChicknWinnerError) {
						return this.loc.chw_error_text;
					}
					if (this.chicknWinnerDailyLimitReached && this.chicknWinnerCounter > 0) {
						return this.loc.chw_daily_limit_msg;
					}
					if (this.chicknWinnerReady) {
						if (this.chicknWinnerCounter === 0) {
							return this.loc.chw_ready_msg;
						} else {
							return this.loc.chw_cooldown_msg;
						}
					} else {
						return this.loc.chw_time_until;
					}
				},
				progressBarWrapClass() {
					if (this.chicknWinnerReady) {
						return 'chw-progress-bar-wrap-complete';
					}
				},
				playAdText() {
					if (this.chicknWinnerReady && this.chicknWinnerCounter === 0) {
						return this.loc.chw_btn_free_reward;
					} else {
						return this.loc.chw_btn_free_reward;
					}
				},
				isEggStoreSaleItem() {
					return this.eggStoreItems.some( item => item['salePrice'] !== '');
				},
			},
			watch : {
				loc(val) {
					this.getLocLang(val);
				},
			}
		});
	}

</script>

	</body>
</html>