function followButton() {

    $(".btn-follow").unbind("click").click(function () {
        $(this).addClass('hidden');
        $(this).parents().find(".btn-unfollow").removeClass("hidden");
        $.ajax({
            url: URL+'/follow',
            type: 'POST',
            data: { followed: $(this).attr('data-followed')},
            success: function (response) {
                console.log(response);
            }
        });
    });

    $(".btn-unfollow").unbind("click").click(function () {
        $(this).addClass('hidden');
        $(this).parents().find(".btn-follow").removeClass("hidden");
        $.ajax({
            url: URL+'/unfollow',
            type: 'POST',
            data: { unfollowed: $(this).attr('data-unfollowed') },
            success: function (response) {
                console.log(response);
            }
        });

    });
}