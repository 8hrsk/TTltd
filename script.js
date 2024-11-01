const submit_btn = document.getElementById("submit");
const data_table = document.getElementById("data");
const user_select = document.getElementById("user");

const table_header = data_table.children[1];
const header_row = table_header.children[0].firstChild

submit_btn.onclick = function (e) {
  e.preventDefault();

  const user = {
    id: user_select.value,
    name: user_select.options[user_select.selectedIndex].text
  };
  
  fetch('/data.php/?user=' + user.id)
    .then(response => response.json())
    .then(data => render_data(user.name, data))
    .catch(error => console.error('Error:', error));
};

function render_data(user_id, data) {
  data_table.children[0].innerHTML = `Transactions of ${user_id}`
  
  clear_table();

  const months = Array();
  for (const month of Object.entries(data)) {
    months.push(month);
  }

  months.forEach(month => {
    create_row(month[0], month[1].balance, month[1].amount);
  });
  
  data_table.style.display = "block";
}

function create_row(month, balance, total) {
    const rowData = `
    <td>${month}</td>
    <td>${balance}</td>
    <td>${total}</td>
  `;

  const row = document.createElement("tr");
  row.insertAdjacentHTML('beforeend', rowData);
  data_table.children[1].appendChild(row);
}


function clear_table() {  
  while (data_table.children[1].firstChild) {
    data_table.children[1].removeChild(data_table.children[1].firstChild);
  }

  table_header.appendChild(header_row);
}