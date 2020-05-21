(() => {
	let _website = null;
	let _feed = null;
	let userInfo = null;
	let apiUrl = 'http://local.api.yourtraffix.com';
	let siteUrl = 'http://localhost:3000';

	addCss(`${siteUrl}/embedded/yt-script.css`);

	const fetchUserInfo = async () => {
		try {
			const response = await fetch('https://api.userinfo.io/userinfos', {
				method: 'get',
				credentials: 'same-origin',
				headers: { 'X-Userinfo-Client-Id': 'userinfo-js:1.1.2-SNAPSHOT' }
			});
			userInfo = await response.json();
		} catch (err) {
			console.error(err);
		}
	};

	if (document.readyState !== 'loading') {
		init();
	} else {
		document.addEventListener('DOMContentLoaded', function() {
			init();
		});
	}

	function init() {
		try {
			setFeedsSettings();
			createUniqIdAndStoreInLocalStorage();
			appendFeeds();
			fetchUserInfo();
			attachedEvent();
		} catch (error) {
			console.error(error);
		}
	}

	const cipher = (salt) => {
		const textToChars = (text) => text.split('').map((c) => c.charCodeAt(0));
		const byteHex = (n) => ('0' + Number(n).toString(16)).substr(-2);
		const applySaltToChar = (code) => textToChars(salt).reduce((a, b) => a ^ b, code);

		return (text) => text.split('').map(textToChars).map(applySaltToChar).map(byteHex).join('');
	};

	function attachedEvent() {
		document.addEventListener('click', (event) => {
			const target = event.target;
			const elem = target.closest('.yt-feed-one');
			if (!elem) return;

			const campaign_id = elem.dataset.campaign_id;
			const visitor_info = cipher('id')(JSON.stringify(userInfo));
			window.open(
				`${apiUrl}/campaigns/click?website_id=${_website.id}&campaign_id=${campaign_id}&visitor_info=${visitor_info}`
			);

			event.preventDefault();
			return false;
		});
	}

	function setFeedsSettings() {}

	function createUniqIdAndStoreInLocalStorage() {
		let uniqId;
		if (localStorage.getItem('uniqId') !== null) {
			uniqId = localStorage.getItem('uniqId');
		} else {
			let newUniqId = Date.now().toString(36) + Math.random().toString(36).substr(2, 5).toUpperCase();
			localStorage.setItem('uniqId', newUniqId);
			uniqId = newUniqId;
		}
	}

	async function appendFeeds() {
		try {
			let response = await fetch(`${apiUrl}/websites/me`);
			if (response.status !== 200) {
				return;
			}

			const website = await response.json();

			_website = website;
			response = await fetch(`${apiUrl}/websites/${website.id}/feed`);
			const { feed } = await response.json();

			_feed = feed;

			console.log([ 'feed.campaigns', feed.campaigns ]);
			const feed_bar = `<div class="yt-feed">
        <div class="yt-feed-header">
          <img src="http://yourtraffix.com/wp-content/uploads/2020/05/sponserdby.png"></img>
        </div>
        <div class="yt-feed-body" style="grid-template-columns: 1fr 1fr 1fr">
             ${feed.campaigns
					.map(
						(campaign) => `
               <a href="${campaign.url}" target="_blank" class="yt-feed-one-title">
                <div class="yt-feed-one" data-campaign_id="${campaign.id}" >
                  <p href="${campaign.url}" target="_blank" class="yt-feed-one-title">${campaign.title}</p>
               
                <div class="yt-feed-one-image">
                   <img src="${campaign.featured_image}"></img></div>
                </div>
             </a>`
					)
					.reduce((feed, campaign) => {
						feed += campaign;
						return feed;
					}, '')}
  
        </div>
      </div>`;

			var div = document.createElement('div');
			div.innerHTML = feed_bar;

			var footer = document.querySelector('footer');
			footer.parentNode.insertBefore(div.querySelector('.yt-feed'), footer);
		} catch (error) {
			console.error(error);
		}
	}

	function addScript(src) {
		var s = document.createElement('script');
		s.setAttribute('src', src);
		document.body.appendChild(s);
	}

	function addCss(src) {
		var link = document.createElement('link');
		link.rel = 'stylesheet';
		link.type = 'text/css';
		link.href = src;
		link.media = 'all';
		document.head.appendChild(link);
	}
})();
