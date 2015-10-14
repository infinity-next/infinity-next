(function() {
	var handleTime = function(time) {
		var timer      = document.getElementById('infdevtimer');
		
		var startDate  = new Date(time);
		var startStamp = startDate.getTime();
		var newDate    = new Date();
		var newStamp   = newDate.getTime();
		var diff       = Math.round((newStamp-startStamp)/1000);
		var diffAbs    = Math.abs(diff);
		
		var d = Math.floor(diffAbs/(24*60*60));
		diffAbs = diffAbs-(d*24*60*60);
		var h = Math.floor(diffAbs/(60*60));
		diffAbs = diffAbs-(h*60*60);
		var m = Math.floor(diffAbs/(60));
		diffAbs = diffAbs-(m*60);
		var s = diffAbs;
		var p = "s";
		
		var funded = "funded for";
		if (diff > 0) {
			funded = "underfunded by";
			timer.style.color = "#C00";
		}
		
		timer.innerHTML = "Infinity Next is " +funded+" "+d+" day"+(d!=1?p:"")+", "+h+" hour"+(h!=1?p:"");
		//+", "+m+" minute"+(m!=1?p:"")+", "+s+" second"+(s!=1?p:"");
		timer.style.display = "block";
	};
	
	var getTime = function() {
		jQuery.ajax("https://infinitydev.org/contribute.json").success(function(data, textStatus, jqXHR) {
			if (typeof data === "object" && typeof data.development_paid_until !== 'undefined') {
				localStorage.setItem('infNextDevTime', new Date());
				localStorage.setItem('infNextDevData', data.development_paid_until);
				handleTime(data.development_paid_until);
			}
		});
	};
	
	var canLocalStorage = false;
	
	try {
		canLocalStorage = typeof window.localStorage !== "undefined";
	}
	catch(err) {}
	
	if (canLocalStorage && localStorage.infNextDevTime) {
		var dateDiff = new Date().getTime() - new Date(localStorage.infNextDevTime).getTime();
		
		if (dateDiff / 1000 >= 600) { // 10 minutes
			localStorage.removeItem('infNextDevTime');
			localStorage.removeItem('infNextDevData');
			getTime();
		}
		else {
			handleTime(localStorage.infNextDevData);
		}
	}
	else {
		getTime();
	}
})();