
"""
read endpoint_params.json
sort items by key
save
"""
from pathlib import Path
import json

file_path = Path(__file__).parent.parent / 'endpoint_params.json'

with open(file_path, 'r') as f:
    data = json.load(f)

data = dict(sorted(data.items()))

with open(file_path, 'w') as f:
    json.dump(data, f, indent=4)
