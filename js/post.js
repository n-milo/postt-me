var like = document.getElementById('like-post-button');
var dislike = document.getElementById('dislike-post-button');
var form = document.getElementById('like-section');
var rating = document.getElementById('rating');

like.onclick = function() {
	submitRating(1);
	return false;
}

dislike.onclick = function() {
	submitRating(-1);
	return false;
}

function submitRating(rating) {
	var req = new XMLHttpRequest();

	req.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			let json = this.responseText;
			console.log(json);
			let res = JSON.parse(json);

			if (res.errors) {
				alert(res.errorMsg);
				return;
			}

			let likes = res.likeChange;
			let dislikes = res.dislikeChange;

			let lc = document.getElementById('like-count')
			lc.innerHTML = parseInt(lc.innerHTML) + likes;
			let dc = document.getElementById('dislike-count')
			dc.innerHTML = parseInt(dc.innerHTML) + dislikes;
			updateLikeBar();

			currentRating = rating;
			updateLikeButtons();
		}
	}

	req.open("POST", "submit_rating.php", true);
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send("post_id=" + post_id + "&rating=" + rating);
}

var likebar = document.getElementById('likes');
function updateLikeBar() {
	let lc = parseInt(document.getElementById('like-count').innerHTML);
	let dc = parseInt(document.getElementById('dislike-count').innerHTML);
	if (lc + dc == 0) {
		var width = 50;
	} else {
		var width = lc / (dc + lc) * 100;
	}
	likebar.style.width = width + "%";
}

function updateLikeButtons() {
	if (currentRating == 1) {
		dislike.classList.remove("active");
		like.classList.add("active");
	} else if (currentRating == -1) {
		dislike.classList.add("active");
		like.classList.remove("active");
	}
}

updateLikeBar();
updateLikeButtons();
