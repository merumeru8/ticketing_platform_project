<script>
    $(document).ready(function () {
        let message = localStorage.getItem('message');

        if(message){
            popupMessage(message);
            localStorage.removeItem('message')
        }

        $("#login_btn").on("click", function(){
            location.href = "/login";
        })

        $("#logout_btn").on("click", function(){
            localStorage.clear();
            location.href = "/logout";
        })

        function searchVal(tableId){
            return $search = $("#search_query_"+tableId).val();
        }

        $(".search_input_go").on("click", function(){
            let parameters = $(this).data("jsparams");
            let tableId = parameters[0];

            let active = $("#active_btn_"+tableId).hasClass("highlighted_btn");

            location.href = $("#"+tableId).data("url") + "?search=" + $(this).siblings("input").val() + (active ? "" : "&deleted=1");
        });

        $(".search_query").on("keypress", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();

                $(this).siblings(".search_input_go").trigger("click");
            }
        });
        
        $(".see_active_btn").on("click", function(){

            let parameters = $(this).data("jsparams");
            let tableId = parameters[0];

            let search = searchVal(tableId);

            location.href = $("#"+tableId).data("url") + (search != "" ? "?search="+ search : "");
        });

        $(".see_archive_btn").on("click", function(){
            let parameters = $(this).data("jsparams");
            let tableId = parameters[0];

            let search = searchVal(tableId);

            location.href = $("#"+tableId).data("url") + "?deleted=1" + (search != "" ? "&search="+ search : "");
        });
    });
</script>
</body>
</html>