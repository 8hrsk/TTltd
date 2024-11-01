const submit_btn = document.getElementById("submit");
const data_table = document.getElementById("data");
const user_select = document.getElementById("user");

submit_btn.onclick = function (e) {
  e.preventDefault();

  // TODO: fetch data
  // TODO: handle errors display none

  const user_id = user_select.value;
  

  fetch('/data.php/?user=' + user_id)
    .then(response => response.json())
    .then(data => console.log(data))
    .catch(error => console.error('Error:', error));

  // TODO: implement
  data_table.style.display = "block";
  // alert("Not implemented");
};

function renderData(data) {
  // TODO: render rows in table
}
