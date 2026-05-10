function searchContractors(input, suggestions) {
    const searchInput = document.getElementById(input);
    const contractorSuggestions = document.getElementById(suggestions);

    document.addEventListener('click', function(event) {
        if (!searchInput.contains(event.target) && !contractorSuggestions.contains(event.target)) {
            contractorSuggestions.classList.add('hidden');
        }
    });

    searchInput.addEventListener('input', function () {

        contractorSuggestions.classList.remove('hidden');
        contractorSuggestions.innerHTML = '';

        const search = this.value;
        if(search === '') {
            contractorSuggestions.innerHTML = '';
            return;
        }

        let searchResults = [];

        contractorList.forEach(item => {
            if(item.toLowerCase().includes(search.toLowerCase())) {
                searchResults.push(item);
            }
        })

        searchResults.forEach(item => {
            const div = document.createElement('div');
            div.textContent = item;
            div.onclick = () => {
                document.getElementById(input).value = div.textContent;
                contractorSuggestions.innerHTML = '';
            };
            contractorSuggestions.appendChild(div);
        });
    });
}